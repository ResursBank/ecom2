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
use Resursbank\Ecom\Module\Payment\Api\GetPayment;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Resursbank\Ecom\Module\Payment\Models\Payment\Application;
use Resursbank\Ecom\Module\Payment\Models\Payment\Identification;
use Resursbank\Ecom\Module\Payment\Models\Payment\Information;
use Resursbank\Ecom\Module\Payment\Models\Payment\Status;
use Resursbank\Ecom\Module\Payment\Repository;

class GetPaymentTest extends TestCase
{
    /**
     * @return void
     * @throws EmptyValueException
     */
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
     *
     * @return string
     */
    private function getStoreId(): string
    {
        return (string)($_ENV['STORE_ID'] ?? '');
    }

    /**
     * This feature will be fixed after findPayments as we need proper payment ids as seen from MAPI.
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
    public function testGetPaymentLive(): void
    {
        if (isset($_ENV['JWT_AUTH_CLIENT_ID']) && $_ENV['JWT_AUTH_CLIENT_ID'] === 'tomas_t') {
            // Temporary solution.
            $orderReference = '9e744903-b9be-431a-a11d-a210f92ecbc3';
            // 20220816073146-1557096130 => 9e744903-b9be-431a-a11d-a210f92ecbc3
            $payment = Repository::getPayment($orderReference);

            static::assertEquals($orderReference, $payment->id);

            return;
        }
        static::markTestSkipped(
            sprintf(
                'Can not run live test for %s since we can not do lookups for orders. They have to be created ' .
                'first. This can be solved with findPayment when/if problem with searching is solved.',
                __FUNCTION__
            )
        );
    }

    /**
     * @return void
     * @throws JsonException
     * @throws ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function testGetPaymentMocked(): void
    {
        $expectedOrderReference = 'testOrderReference';
        $getPayment = $this->createMock(
            originalClassName: GetPayment::class
        );

        $payment = new Payment(
            $expectedOrderReference,
            created: '2022-08-16T09:31:47.829',
            storeId: 'storeId',
            paymentMethodId: 'paymentMethodId',
            customer: new Payment\Customer(
                deliveryAddress: new Payment\Address(
                    'Full Name',
                    addressRow1: 'Glassgatan 17',
                    postalArea: 'Göteborg',
                    postalCode: '12345',
                    addressRow2: ''
                ),
                email: 'test@test.com',
                governmentId: '8305147715',
                mobilePhone: '0701122334',
                phone: '0701122334',
                customerType: 'NATURAL',
                identification: new Identification(
                    type: 'ID',
                    reference: '123'
                )
            ),
            status: new Status(
                value: 'string',
                possibleActions: []
            ),
            paymentActions: [],
            application: new Application(
                approvedCreditLimit: 1000,
                requestedCreditLimit: 1000,
                reference: 1000
            ),
            information: new Information(
                creator: 'username'
            ),
            countryCode: 'SE'
        );
        $getPayment->method('call')->willReturn($payment);
        $response = $getPayment->call($expectedOrderReference);
        static::assertTrue(
            condition: $response instanceof Payment &&
            $response->id === $expectedOrderReference
        );
    }
}
