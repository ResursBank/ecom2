<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

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

/**
 * Test that searchPayment works.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SearchTest extends TestCase
{
    private const GOVERNMENT_ID = '198305147715';

    /**
     * @throws EmptyValueException
     * @SuppressWarnings(PHPMD.Superglobals)
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
     * @param string $orderReference
     * @return Payment
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createPayment(string $orderReference): Payment
    {
        return Repository::create(
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
                governmentId: self::GOVERNMENT_ID,
                mobilePhone: '46701234567',
                deviceInfo: new Customer\DeviceInfo()
            )
        );
    }

    /**
     * Reference is currently required to have if we want to run live tests.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testSearchOrderReference(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        $paymentCollection = Repository::search(
            storeId: (string) $_ENV['STORE_ID'],
            orderReference: $orderReference
        )->toArray();

        /** @var Payment|null $fetched */
        $fetched = $paymentCollection[0] ?? null;

        self::assertSame(
            expected: $payment->id,
            actual: $fetched !== null ? $fetched->id : ''
        );
    }

    /**
     * @throws ValidationException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testSearchWithGovernmentId(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        $paymentCollection = Repository::search(
            storeId: (string) $_ENV['STORE_ID'],
            orderReference: $orderReference,
            governmentId: self::GOVERNMENT_ID
        )->toArray();

        $fetched = $paymentCollection[0] ?? null;

        self::assertSame(
            expected: $payment->id,
            actual: $fetched !== null ? $fetched->id : ''
        );
    }
}
