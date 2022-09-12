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
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Data\Models\Address;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Api\GetPayment;
use Resursbank\Ecom\Module\Payment\Enum\Status;
use Resursbank\Ecom\Module\Payment\Models\Payment;
use Resursbank\Ecom\Module\Payment\Models\Payment\Application;
use Resursbank\Ecom\Module\Payment\Models\Payment\Customer;
use Resursbank\Ecom\Module\Payment\Models\Payment\Identification;
use Resursbank\Ecom\Module\Payment\Models\Payment\Information;
use Resursbank\Ecom\Module\Payment\Repository;
use TypeError;

class GetPaymentTest extends TestCase
{
    /**
     * Generic uuid for mocked stores (storeId).
     * @var string $expectedStoreId
     */
    private string $expectedStoreId = 'febe5ddc-e4fa-4017-89f2-ae741930e9cf';

    /**
     * Generic uuid for mocked order references.
     * @var string $expectedOrderReference
     */
    private string $expectedOrderReference = '92678aea-c7a2-4ec5-b5b5-406789610f63';

    /**
     * Generic uuid for mocked payment methods.
     * @var string $expectedPaymentMethod
     */
    private string $expectedPaymentMethod = '22273236-7cb8-4f09-9044-36c80d5c3649';

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

//    /**
//     * @return void
//     * @throws JsonException
//     * @throws ReflectionException
//     * @throws AuthException
//     * @throws CurlException
//     * @throws ValidationException
//     * @throws EmptyValueException
//     * @throws IllegalTypeException
//     * @noinspection PhpConditionAlreadyCheckedInspection
//     */
//    public function testGetPaymentMocked(): void
//    {
//        $getPayment = $this->createMock(
//            originalClassName: GetPayment::class
//        );
//
//        $payment = new Payment(
//            $this->expectedOrderReference,
//            created: '2022-08-16T09:31:47.829',
//            storeId: $this->expectedStoreId,
//            paymentMethodId: $this->expectedPaymentMethod,
//            customer: new Customer(
//                email: 'test@test.com',
//                governmentId: '8305147715',
//                mobilePhone: '0701122334',
//                phone: '0701122334',
//                customerType: 'NATURAL',
//                deliveryAddress: new Address(
//                    fullName: 'Full Name',
//                    addressRow1: 'Glassgatan 17',
//                    postalArea: 'Göteborg',
//                    postalCode: '12345',
//                    addressRow2: ''
//                ),
//                identification: new Identification(
//                    type: 'ID',
//                    reference: '123'
//                )
//            ),
//            status: Status::ACCEPTED,
//            paymentActions: [],
//            application: new Application(
//                approvedCreditLimit: 1000,
//                requestedCreditLimit: 1000,
//                reference: 1000
//            ),
//            information: new Information(
//                creator: 'username'
//            ),
//            countryCode: 'SE'
//        );
//        $getPayment->method('call')->willReturn($payment);
//        $response = $getPayment->call($this->expectedOrderReference);
//        static::assertTrue(
//            condition: $response instanceof Payment &&
//            $response->id === $this->expectedOrderReference
//        );
//    }

    /**
     * Bad customer test, for which the customer object for some reason is empty on the request.
     *
     * @return void
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function testGetPaymentMockedBadCustomer(): void
    {
        static::expectException(TypeError::class);
        $expectedOrderReference = '92678aea-c7a2-4ec5-b5b5-406789610f63';
        $this->createMock(
            originalClassName: GetPayment::class
        );

        new Payment(
            $expectedOrderReference,
            created: '2022-08-16T09:31:47.829',
            storeId: $this->expectedStoreId,
            paymentMethodId: $this->expectedPaymentMethod,
            customer: null,
            status: Status::ACCEPTED,
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
    }
}
