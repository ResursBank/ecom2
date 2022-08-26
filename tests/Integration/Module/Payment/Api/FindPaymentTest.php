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
use Resursbank\Ecom\Module\Payment\Repository;

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
        return (string)($_ENV['MERCHANT_STORE_ID'] ?? '');
    }

    /**
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws TypeException
     * @throws ValidationException
     * @throws ReflectionException
     * @todo Reference is currently required to have if we want to run live tests.
     * @todo Reported: findPayments should be able to find at least the last payments rendered for the current store.
     */
    public function testFindPaymentLive()
    {
        if (isset($_ENV['JWT_AUTH_CLIENT_ID']) && $_ENV['JWT_AUTH_CLIENT_ID'] === 'tomas_t') {
            $orderReference = '20220816073146-1557096130';
            $expectedId = '9e744903-b9be-431a-a11d-a210f92ecbc3';
            if (!empty($orderReference)) {
                $paymentCollection = Repository::findPayment(
                    $this->getStoreId(),
                    $orderReference
                );

                $payment = $paymentCollection->current();
                static::assertSame($expectedId, $payment->id);
                return;
            }
        }
        static::markTestSkipped(
            sprintf(
                'Can not run live test for %s since we can not do a proper search for random orders. Current ' .
                'search is restricted to specific orders only.',
                __FUNCTION__
            )
        );
    }
}
