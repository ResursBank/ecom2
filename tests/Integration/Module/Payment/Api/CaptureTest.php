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
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Api\Capture;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Tests for MAPI Payment Capture class.
 */
class CaptureTest extends TestCase
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
     * Verify that capturing an entire order works
     *
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testCaptureEntirePayment(): void
    {
        $orderReference = Strings::generateRandomString(length: 12);
        // Create payment
        $payment = $this->createPayment(orderReference: $orderReference);
        $originalId = $payment->id;

        // Sign
        MockSigner::callCustomerUrl(payment: $payment);

        // Capture payment
        $response = Repository::capture(paymentId: $originalId);

        // Assert that payment has been captured in full
        $this->assertNotNull(actual: $response->order);
        $this->assertEquals(expected: $originalId, actual: $response->id);
        $this->assertEquals(
            expected: $response->order->totalOrderAmount,
            actual: $response->order->capturedAmount
        );
    }

    /**
     * Verify that capturing a single specified order line works
     *
     * @throws Exception
     */
    public function testCaptureSingleOrderLine(): void
    {
        $orderReference = Strings::generateRandomString(length: 12);
        // Create payment with multiple order lines
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::callCustomerUrl(payment: $payment);

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

        // Capture single order line
        $response = Repository::capture(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        // Assert that only this order line has been captured
        $this->assertEquals(expected: $payment->id, actual: $response->id);
        $this->assertNotNull(actual: $response->order);
        $this->assertCount(
            expectedCount: 2,
            haystack: $response->order->actionLog
        );
    }

    /**
     * Verify that capturing with a transaction ID works
     *
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testCaptureWithTransactionId(): void
    {
        $orderReference = Strings::generateRandomString(length: 12);
        // Create payment
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::callCustomerUrl(payment: $payment);

        // Capture and specify transaction id
        $transactionId = Strings::generateRandomString(length: 12);
        $response = Repository::capture(
            paymentId: $payment->id,
            transactionId: $transactionId
        );

        // Verify that capture worked as intended
        $this->assertNotNull(actual: $response->order);
        $this->assertTrue(condition: isset($response->order->actionLog[1]));

        $actionLog = $response->order->actionLog[1];

        $this->assertInstanceOf(expected: ActionLog::class, actual:$actionLog);
        $this->assertEquals(
            expected: $transactionId,
            actual: $actionLog->transactionId
        );
    }

    /**
     * Verify that capturing with an invoice ID works
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testCaptureWithInvoiceId(): void
    {
        $orderReference = Strings::generateRandomString(length: 12);
        // Create payment
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::callCustomerUrl(payment: $payment);

        // Capture and specify transaction id
        $invoiceId = Strings::generateRandomString(length: 12);
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
        $response = Repository::capture(
            paymentId: $payment->id,
            orderLines: $orderLines,
            invoiceId: $invoiceId
        );

        // Verify that capture worked as intended
        $this->assertNotNull(actual: $response->order);
        $this->assertEquals(expected: $payment->id, actual: $response->id);
        $this->assertCount(
            expectedCount: 2,
            haystack: $response->order->actionLog
        );
    }

    /**
     * Verify getCapturedAmount behavior.
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
    public function testGetCapturedAmount(): void
    {
        // Create payment with multiple order lines
        $orderReference = Strings::generateRandomString(length: 12);
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::callCustomerUrl(payment: $payment);

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

        // Capture single order line
        $response = Repository::capture(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        $capture = new Capture();

        $this->assertEquals(
            expected: $response->order?->capturedAmount,
            actual: $capture->getCapturedAmount(paymentId: $payment->id)
        );

        // Verify that an invalid payment still gives 0.0 as a response.
        $this->assertEquals(
            expected: 0.0,
            actual: $capture->getCapturedAmount(
                paymentId: Strings::generateRandomString(length: 12)
            )
        );
    }
}
