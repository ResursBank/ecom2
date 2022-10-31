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
use Resursbank\EcomTest\Utilities\MockSigner;
use Resursbank\Ecom\Module\Payment\Enum\ActionType;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Customer;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\DeliveryAddress;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLine;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection as ActionLogOrderLineCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine as ActionLogOrderLine;

/**
 * Tests for MAPI Payment Cancel class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class CancelTest extends TestCase
{
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
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createPayment(string $orderReference): Payment
    {
        /** @noinspection DuplicatedCode */
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
                governmentId: '198305147715',
                mobilePhone: '46701234567',
                deviceInfo: new Customer\DeviceInfo()
            )
        );
    }

    /**
     * Verify that canceling an entire payment works as intended
     * @return void
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
    public function testCancelEntirePayment(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Cancel payment
        $response = Repository::cancel(paymentId: $payment->id);

        // Assert that cancel went through
        $this->assertEquals(
            expected: $payment->id,
            actual: $response->id
        );
        $this->assertNotNull(actual: $response->order);
        $this->assertNotNull(actual: $payment->order);
        /** @psalm-suppress MixedPropertyFetch */
        $this->assertEquals(
            expected: ActionType::CANCEL,
            actual: $response->order->actionLog[1]->type
        );
        $this->assertEquals(
            expected: $payment->order->totalOrderAmount,
            actual: $response->order->totalOrderAmount
        );
        $this->assertEquals(
            expected: $response->order->totalOrderAmount,
            actual: $response->order->canceledAmount
        );
    }

    /**
     * Verify that cancelling a single order line works as intended
     *
     * @return void
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
    public function testCancelWithOrderLines(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Cancel one order line
        $orderLine = new ActionLogOrderLine(
            description: 'Android',
            reference: 'T-800',
            quantityUnit: 'st',
            quantity: 2.00,
            vatRate: 25.00,
            unitAmountIncludingVat: 150.75,
            totalAmountIncludingVat: 301.5,
            totalVatAmount: 60.3,
            type: OrderLineType::PHYSICAL_GOODS
        );
        $response = Repository::cancel(
            paymentId: $payment->id,
            orderLines: new ActionLogOrderLineCollection(data: [$orderLine])
        );

        // Assert that cancel went through
        $this->assertEquals(
            expected: $payment->id,
            actual: $response->id
        );
        $this->assertNotNull(actual: $response->order);
        $this->assertNotNull(actual: $payment->order);
        /**
         * @psalm-suppress MixedPropertyFetch
         * @psalm-suppress MixedArrayAccess
         */
        $this->assertEquals(
            expected: $payment->order->actionLog[0]->orderLines[0],
            actual: $response->order->actionLog[1]->orderLines[0]
        );
        /**
         * @psalm-suppress MixedPropertyFetch
         * @psalm-suppress MixedArrayAccess
         */
        $this->assertEquals(
            expected: $payment->order->actionLog[0]->orderLines[0]->totalAmountIncludingVat,
            actual: $response->order->canceledAmount
        );
    }

    /**
     * Verify that canceling with creator argument results in specified creator value being present in action log
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
    public function testCancelWithCreator(): void
    {
        // Create payment
        $orderReference = $this->generateOrderReference();
        $payment = $this->createPayment(orderReference: $orderReference);

        // Sign
        MockSigner::approve(payment: $payment);

        // Cancel order
        $creator = 'Foobar';
        $response = Repository::cancel(
            paymentId: $payment->id,
            creator: $creator
        );

        // Assert that creator argument is present in action log
        $this->assertEquals(
            expected: $payment->id,
            actual: $response->id
        );
        $this->assertNotNull(
            actual: $response->order
        );
        /** @psalm-suppress MixedPropertyFetch */
        $this->assertEquals(
            expected: $creator,
            actual: $response->order->actionLog[1]->creator
        );
    }
}
