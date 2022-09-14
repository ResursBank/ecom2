<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api;

use Exception;
use PHPUnit\Framework\TestCase;
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
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Customer;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\DeliveryAddress;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection as ActionLogOrderLineCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine as ActionLogOrderLine;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Repository;

/**
 * Tests for MAPI Payment Capture class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CapturePaymentTest extends TestCase
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
     * @todo Implement mock signing
     * @param Payment $payment
     * @return void
     */
    private function mockSign(Payment $payment): void
    {
        $curlHandle = curl_init(url: $payment->taskRedirectionUrls->customerUrl);
        curl_setopt(handle: $curlHandle, option: CURLOPT_HEADER, value: true);
        curl_setopt(handle: $curlHandle, option: CURLOPT_FOLLOWLOCATION, value: true);
        curl_setopt(handle: $curlHandle, option: CURLOPT_RETURNTRANSFER, value: true);
        curl_exec(handle: $curlHandle);
        $redirectUrl = curl_getinfo(handle: $curlHandle, option: CURLINFO_EFFECTIVE_URL);
        curl_close(handle: $curlHandle);

        $realAuthUrl = str_replace("authenticate", 'doAuth', $redirectUrl) .
            '&govId=' . $payment->customer->governmentId;

        $curlHandle = curl_init(url: $realAuthUrl);
        curl_exec(handle: $curlHandle);
    }

    /**
     * Verify that capturing an entire order works
     *
     * @return void
     * @throws EmptyValueException
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testCaptureEntirePayment(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment
        $payment = Repository::createPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
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
        $originalId = $payment->id;

        // Sign
        $this->mockSign(payment: $payment);

        // Capture payment
        $response = Repository::capture(paymentId: $originalId);

        // Assert that payment has been captured in full
        $this->assertNotNull(
            actual: $response->order
        );
        $this->assertEquals(
            expected: $originalId,
            actual: $response->id
        );
        $this->assertEquals(
            expected: $response->order->totalOrderAmount,
            actual: $response->order->capturedAmount
        );
    }

    /**
     * Verify that capturing a single specified order line works
     *
     * @return void
     * @throws Exception
     */
    public function testCaptureSingleOrderLine(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment with multiple order lines
        $payment = Repository::createPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
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

        $this->mockSign(payment: $payment);

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

        // Capture single order line
        $response = Repository::capture(
            paymentId: $payment->id,
            orderLines: $orderLines
        );

        // Assert that only this order line has been captured
        $this->assertEquals(
            expected: $payment->id,
            actual: $response->id
        );
        $this->assertNotNull(
            actual: $response->order
        );
        $this->assertCount(
            expectedCount: 2,
            haystack: $response->order->actionLog
        );
    }

    /**
     * Verify that capturing with a transaction ID works
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws ApiException
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testCaptureWithTransactionId(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment
        $payment = Repository::createPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
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

        // Sign
        $this->mockSign(payment: $payment);

        // Capture and specify transaction id
        $transactionId = $this->generateOrderReference();
        $response = Repository::capture(
            paymentId: $payment->id,
            transactionId: $transactionId
        );

        // Verify that capture worked as intended
        $this->assertNotNull(
            actual: $response->order
        );
        $this->assertEquals(
            expected: $transactionId,
            actual: $response->order->actionLog[1]->transactionId
        );
    }

    /**
     * Verify that capturing with an invoice ID works
     *
     * @return void
     */
    public function testCaptureWithInvoiceId(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment
        $payment = Repository::createPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
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

        // Sign
        $this->mockSign(payment: $payment);

        // Capture and specify transaction id
        $invoiceId = $this->generateOrderReference();
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
        $response = Repository::capture(
            paymentId: $payment->id,
            orderLines: $orderLines,
            invoiceId: $invoiceId
        );

        // Verify that capture worked as intended
        $this->assertNotNull(
            actual: $response->order
        );
        $this->assertEquals(
            expected: $payment->id,
            actual: $response->id
        );
        $this->assertCount(
            expectedCount: 2,
            haystack: $response->order->actionLog
        );
    }
}
