<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection EfferentObjectCouplingInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\MockSigner;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Customer;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\DeliveryAddress;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLine;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine as ActionLogOrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection as ActionLogOrderLineCollection;

/**
 * Tests for MAPI Payment Refund class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class RefundTest extends TestCase
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
     * Generate a dummy order reference
     *
     * @return string
     * @throws Exception
     */
    private function generateOrderReference(): string
    {
        return bin2hex(string: random_bytes(length: 12));
    }

    /**
     * Make API call to create payment
     *
     * @param string $orderReference
     * @return Payment
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function createPayment(string $orderReference): Payment
    {
        /** @noinspection DuplicatedCode */
        return Repository::createPayment(
            storeId: (string) $_ENV['STORE_ID'],
            paymentMethodId: (string) $_ENV['PAYMENT_METHOD_ID'],
            orderLines: new OrderLineCollection(data: [
                new OrderLine(
                    description: 'Android',
                    reference: 'T-800',
                    quantityUnit: 'st',
                    quantity: 2.00,
                    vatRate: 25.00,
                    unitAmountIncludingVat: 150.75,
                    totalAmountIncludingVat: 301.5,
                    totalVatAmount: 60.3,
                    type: OrderLineType::PHYSICAL_GOODS
                ),
                new OrderLine(
                    description: 'Robot',
                    reference: 'T-1000',
                    quantityUnit: 'st',
                    quantity: 2.00,
                    vatRate: 25.00,
                    unitAmountIncludingVat: 150.75,
                    totalAmountIncludingVat: 301.5,
                    totalVatAmount: 60.3,
                    type: OrderLineType::PHYSICAL_GOODS
                )
            ]),
            orderReference: $orderReference,
            customer: new Customer(
                deliveryAddress: new DeliveryAddress(
                    addressRow1: 'Glassgatan 15',
                    postalArea: 'Göteborg',
                    postalCode: '41655',
                    countryCode: CountryCode::SE
                ),
                customerType: CustomerType::NATURAL,
                contactPerson: 'Vincent',
                email: 'test@hosted.resurs',
                governmentId: '198305147715',
                mobilePhone: '46701234567',
                deviceInfo: new Customer\DeviceInfo()
            )
        );
    }

    /**
     * Verify that refunding an entire order works as intended
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ValidationException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testRefundEntirePayment(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture payment
        Repository::capture(paymentId: $payment->id);

        // Refund entire payment
        $refundResponse = Repository::refund(paymentId: $payment->id);

        // Assert that entire payment has been refunded
        self::assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        self::assertNotNull(
            actual: $refundResponse->order
        );
        self::assertNotNull(
            actual: $payment->order
        );
        self::assertEquals(
            expected: $payment->order->totalOrderAmount,
            actual: $refundResponse->order->refundedAmount
        );
    }

    /**
     * Verify that refunding a single captured order line works
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Exception
     */
    public function testRefundSingleOrderLine(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund single order line
        $orderLines = new ActionLogOrderLineCollection([
            new ActionLogOrderLine(
                description: 'Android',
                reference: 'T-800',
                quantityUnit: 'st',
                quantity: 2.00,
                vatRate: 25.00,
                unitAmountIncludingVat: 150.75,
                totalAmountIncludingVat: 301.5,
                totalVatAmount: 60.3,
                type: OrderLineType::PHYSICAL_GOODS
            )
        ]);
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        // Assert that only specified order line has been refunded
        self::assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        self::assertNotNull(
            actual: $refundResponse->order
        );
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertEquals(
            expected: $orderLines[0]->totalAmountIncludingVat,
            actual: $refundResponse->order->refundedAmount
        );
    }

    /**
     * Verify that refunding with a transaction id works
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Exception
     */
    public function testRefundWithTransactionId(): void
    {
        // Create payment
        /** @noinspection DuplicatedCode */
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund
        $transactionId = $this->generateOrderReference();
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            transactionId: $transactionId
        );

        // Assert that transaction id is present in action log
        self::assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        self::assertNotNull(actual: $refundResponse->order);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertEquals(
            expected: $transactionId,
            actual: $refundResponse->order->actionLog[2]->transactionId
        );
    }

    /**
     * Verify that refunding with creator specified works
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Exception
     */
    public function testRefundWithCreator(): void
    {
        // Create payment
        /** @noinspection DuplicatedCode */
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund
        $creator = $this->generateOrderReference();
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            creator: $creator
        );

        // Assert that transaction id is present in action log
        self::assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        self::assertNotNull(actual: $refundResponse->order);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertEquals(
            expected: $creator,
            actual: $refundResponse->order->actionLog[2]->creator
        );
    }
}
