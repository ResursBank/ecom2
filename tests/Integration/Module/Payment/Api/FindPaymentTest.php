<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Resursbank\Ecom\Module\Payment\Repository;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class FindPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: (string)$_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string)$_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string)$_ENV['JWT_AUTH_SCOPE'],
                grantType: (string)$_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );
    }

    /**
     * Set a store id if phpunit.xml has one (for find_payments).
     * @return string
     */
    private function getStoreId()
    {
        return (string)($_ENV['STORE_ID'] ?? '');
    }

    /**
     * Special functions that makes sure some of the tests being made here is limited to a specific account.
     * This will be changed when we find a simpler way to search for payments.
     * @return bool
     */
    private function verifyLiveAccount()
    {
        return isset($_ENV['JWT_AUTH_CLIENT_ID']) && $_ENV['JWT_AUTH_CLIENT_ID'] === 'tomas_t';
    }

    /**
     * @return void
     */
    private function markLiveAccountSkipped($func)
    {
        if (!$this->verifyLiveAccount()) {
            static::markTestSkipped(
                sprintf(
                    'Can not run live test for %s since we can not do a proper search for random orders. Current ' .
                    'search is restricted to specific orders only.',
                    $func
                )
            );
        }
    }

    /**
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws InvalidTypeException
     * @throws ValidationException
     * @throws ReflectionException
     * @todo Reference is currently required to have if we want to run live tests.
     * @todo Reported: findPayments should be able to find at least the last payments rendered for the current store.
     */
    public function testFindPaymentLive()
    {
        $orderReference = '20220816073146-1557096130';
        $expectedId = '9e744903-b9be-431a-a11d-a210f92ecbc3';

        if ($this->verifyLiveAccount()) {
            if (!empty($orderReference)) {
                $paymentCollection = Repository::findPayment(
                    $this->getStoreId(),
                    $orderReference
                );

                $payment = $paymentCollection->current();
                static::assertTrue(
                    $expectedId === $payment->id &&
                    $payment->customer->customerType === 'NATURAL'
                );
            }
        }
        $this->markLiveAccountSkipped(__FUNCTION__);
    }

    /**
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFindPaymentCompany()
    {
        $orderReference = '20220829085222-RC31538721';
        $expectedId = 'f3b7dd6b-dc21-4813-9b94-99ffeb4b28d0';

        if ($this->verifyLiveAccount()) {
            if (!empty($orderReference)) {
                $paymentCollection = Repository::findPayment(
                    $this->getStoreId(),
                    $orderReference
                );

                /** @var Payment $payment */
                $payment = $paymentCollection->current();

                static::assertTrue(
                    $expectedId === $payment->id &&
                    $payment->customer->customerType === 'LEGAL'
                );
            }
        }
        $this->markLiveAccountSkipped(__FUNCTION__);
    }

    /**
     * Testing to find a payment that has a different delivery address than the billing address.
     * This test is not checking nor expecting anything but the delivery block as of aug -22, this
     * test is only here to make it easier to confirm that.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testBillingDeliveryNatural()
    {
        $orderReference = '20220829092623-RC84384074';
        $expectedId = '6f3269c4-30df-429e-898b-7a63371422b5';

        if ($this->verifyLiveAccount()) {
            if (!empty($orderReference)) {
                $paymentCollection = Repository::findPayment(
                    $this->getStoreId(),
                    $orderReference
                );

                /** @var Payment $payment */
                $payment = $paymentCollection->current();

                static::assertTrue(
                    $expectedId === $payment->id &&
                    $payment->customer->customerType === 'NATURAL'
                );
            }
        }
        $this->markLiveAccountSkipped(__FUNCTION__);
    }
}
