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
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Customer;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\DeliveryAddress;
use Resursbank\Ecom\Module\Payment\Models\Order\OrderLine;
use Resursbank\Ecom\Module\Payment\Models\Order\OrderLineCollection;
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
        // @todo Create payment when we have a createPayment method available
        $order = Repository::createPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: new OrderLineCollection(data: [
                new OrderLine(
                    description: 'asdasdasd',
                    reference: 'T-800',
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

        // Capture payment
        $originalId = $order->id;
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
    public function testCaptureSingleOrderline(): void
    {
        $orderReference = $this->generateOrderReference();
        // Create payment with multiple order lines

        // Capture single order line
        $originalId = '';
        $response = Repository::capture(
            paymentId: $originalId,
            orderLines: $orderLines
        );

        // Assert that only this order line has been captured
        $this->assertEquals(
            expected: $originalId,
            actual: $response->id
        );
    }

    /**
     * Verify that capturing with a transaction ID works
     *
     * @return void
     */
    public function testCaptureWithTransactionId(): void
    {
        // Create payment

        // Capture and specify transaction id

        // Verify that capture worked as intended
    }

    /**
     * Verify that capturing with an invoice ID works
     *
     * @return void
     */
    public function testCaptureWithInvoiceId(): void
    {
    }
}
