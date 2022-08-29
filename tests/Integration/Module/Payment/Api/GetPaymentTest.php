<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Api\GetPayment;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Resursbank\Ecom\Module\Payment\Repository;

class GetPaymentTest extends TestCase
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
            ),
            storeId: $this->getStoreId()
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
     * This feature will be fixed after findPayments as we need proper payment ids as seen from MAPI.
     * @return void
     * @throws TypeException
     */
    public function testGetPaymentLive()
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

    public function testGetPaymentMocked()
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
            paymentActions: [],
            customer: new Payment\Customer(),
            status: new Payment\Status(
                value: 'string',
                possibleActions: []
            ),
            information: new Payment\Information(
                creator: 'username'
            ),
            application: new Payment\Application(
                approvedCreditLimit: 1000,
                requestedCreditLimit: 1000,
                reference: 1000
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
