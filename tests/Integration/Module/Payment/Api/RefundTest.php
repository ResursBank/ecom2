<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection EfferentObjectCouplingInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TimeoutException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\CountryCode;
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\OrderLineType;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Utilities\MockSigner;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Api\Refund;
use Resursbank\Ecom\Module\Payment\Repository;

/**
 * Tests for MAPI Payment Refund class.
 */
class RefundTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );
    }

    /**
     * Make API call to create payment
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @throws AttributeCombinationException
     */
    private function createPayment(string $orderReference): Payment
    {
        /** @noinspection DuplicatedCode */
        return Repository::create(
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: new OrderLineCollection(data: [
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Android',
                    reference: 'T-800',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Robot',
                    reference: 'T-1000',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
            ]),
            orderReference: $orderReference,
            customer: new Customer(
                deliveryAddress: new Address(
                    addressRow1: 'Glassgatan 15',
                    postalArea: 'Göteborg',
                    postalCode: '41655',
                    countryCode: CountryCode::SE
                ),
                customerType: CustomerType::NATURAL,
                contactPerson: 'Vincent',
                email: 'test@hosted.resurs.com',
                governmentId: '198305147715',
                mobilePhone: '0701234567',
                deviceInfo: new DeviceInfo()
            ),
            metadata: MockSigner::getMetadata()
        );
    }

    /**
     * Verify that refunding an entire order works as intended
     *
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
     * @todo This test will sometimes fail, stating received timestamp is not valid. On separate re-run worked fine.
     */
    public function testRefundEntirePayment(): void
    {
        // Create payment
        $orderReference = Strings::generateRandomString(length: 12);
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        try {
            MockSigner::callCustomerUrl(payment: $payment);
        } catch (TimeoutException $error) {
            if ($_ENV['IS_PIPELINE']) {
                $this->markTestSkipped(
                    message: 'MockSigner failed with timeout.'
                );
            }

            throw $error;
        }

        // Capture payment
        Repository::capture(paymentId: $payment->id);

        // Refund entire payment
        $refundResponse = Repository::refund(paymentId: $payment->id);

        // Assert that entire payment has been refunded
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);
        $this->assertNotNull(actual: $payment->order);
        $this->assertEquals(
            expected: $payment->order->totalOrderAmount,
            actual: $refundResponse->order->refundedAmount
        );
    }

    /**
     * Verify that refunding a single captured order line works
     *
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
        $orderReference = Strings::generateRandomString(length: 12);
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        try {
            MockSigner::callCustomerUrl(payment: $payment);
        } catch (TimeoutException $error) {
            if ($_ENV['IS_PIPELINE']) {
                $this->markTestSkipped(
                    message: 'MockSigner failed with timeout.'
                );
            }

            throw $error;
        }

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund single order line
        $orderLines = new OrderLineCollection(data: [
            new OrderLine(
                quantity: 2.00,
                quantityUnit: 'st',
                vatRate: 25.00,
                totalAmountIncludingVat: 301.5,
                description: 'Android',
                reference: 'T-800',
                type: OrderLineType::PHYSICAL_GOODS,
                unitAmountIncludingVat: 150.75,
                totalVatAmount: 60.3
            ),
        ]);
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        // Assert that only specified order line has been refunded
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);

        $orderLine = $orderLines[0];

        $this->assertInstanceOf(expected: OrderLine::class, actual: $orderLine);

        $this->assertEquals(
            expected: $orderLine->totalAmountIncludingVat,
            actual: $refundResponse->order->refundedAmount
        );
    }

    /**
     * Verify that refunding with a transaction id works
     *
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
        $orderReference = Strings::generateRandomString(length: 12);
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        try {
            MockSigner::callCustomerUrl(payment: $payment);
        } catch (TimeoutException $error) {
            if ($_ENV['IS_PIPELINE']) {
                $this->markTestSkipped(
                    message: 'MockSigner failed with timeout.'
                );
            }

            throw $error;
        }

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund
        $transactionId = Strings::generateRandomString(length: 12);
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            transactionId: $transactionId
        );

        // Assert that transaction id is present in action log
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);
        $this->assertTrue(
            condition: isset($refundResponse->order->actionLog[2])
        );

        $actionLog = $refundResponse->order->actionLog[2];

        $this->assertInstanceOf(expected: ActionLog::class, actual: $actionLog);

        $this->assertEquals(
            expected: $transactionId,
            actual: $actionLog->transactionId
        );
    }

    /**
     * Verify that refunding with creator specified works
     *
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
        $orderReference = Strings::generateRandomString(length: 12);
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        try {
            MockSigner::callCustomerUrl(payment: $payment);
        } catch (TimeoutException $error) {
            if ($_ENV['IS_PIPELINE']) {
                $this->markTestSkipped(
                    message: 'MockSigner failed with timeout.'
                );
            }

            throw $error;
        }

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund
        $creator = Strings::generateRandomString(length: 12);
        $refundResponse = Repository::refund(
            paymentId: $payment->id,
            creator: $creator
        );

        // Assert that transaction id is present in action log
        $this->assertEquals(
            expected: $payment->id,
            actual: $refundResponse->id
        );
        $this->assertNotNull(actual: $refundResponse->order);
        $this->assertTrue(
            condition: isset($refundResponse->order->actionLog[2])
        );

        $actionLog = $refundResponse->order->actionLog[2];

        $this->assertInstanceOf(expected: ActionLog::class, actual: $actionLog);

        $this->assertEquals(expected: $creator, actual: $actionLog->creator);
    }

    /**
     * Verify getRefundedAmount behavior.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     * @throws NotJsonEncodedException
     * @throws Exception
     */
    public function testGetRefundedAmount(): void
    {
        // Create payment
        $orderReference = Strings::generateRandomString(length: 12);
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        try {
            MockSigner::callCustomerUrl(payment: $payment);
        } catch (TimeoutException $error) {
            if ($_ENV['IS_PIPELINE']) {
                $this->markTestSkipped(
                    message: 'MockSigner failed with timeout.'
                );
            }

            throw $error;
        }

        // Capture
        Repository::capture(paymentId: $payment->id);

        // Refund single order line
        $orderLines = new OrderLineCollection(data: [
            new OrderLine(
                quantity: 2.00,
                quantityUnit: 'st',
                vatRate: 25.00,
                totalAmountIncludingVat: 301.5,
                description: 'Android',
                reference: 'T-800',
                type: OrderLineType::PHYSICAL_GOODS,
                unitAmountIncludingVat: 150.75,
                totalVatAmount: 60.3
            ),
        ]);
        $response = Repository::refund(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        $refund = new Refund();
        $this->assertEquals(
            expected: $response->order?->refundedAmount,
            actual: $refund->getRefundedAmount(paymentId: $response->id)
        );

        // Verify that an invalid payment still gives 0.0 as a response.
        $this->assertEquals(
            expected: 0.0,
            actual: $refund->getRefundedAmount(
                paymentId: Strings::generateRandomString(length: 12)
            )
        );
    }
}
