<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLine;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Order\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Repository;

/**
 * Integration tests for CreatePayment repository.
 */
class RepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: $_ENV['JWT_AUTH_SCOPE'],
                grantType: $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        parent::setUp();
    }

    /**
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
     * @throws ConfigException
     */
    public function testCreatePayment(): void
    {
        $orderLines = new OrderLineCollection(
            data: [
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
            ]
        );

        $createdOrder = Repository::create(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: $orderLines
        );

        if (!isset($createdOrder->order->actionLog[0])) {
            throw new MissingKeyException(message: 'actionLog contains no entries');
        }

        /** @var \Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine $createdOrderLine */
        $createdOrderLine = $createdOrder->order->actionLog[0]->orderLines->toArray()[0];

        $this->assertEquals(
            expected: $orderLines[0]->description,
            actual: $createdOrderLine->description
        );
        $this->assertEquals(
            expected: $orderLines[0]->vatRate,
            actual: $createdOrderLine->vatRate
        );
        $this->assertEquals(
            expected: $orderLines[0]->reference,
            actual: $createdOrderLine->reference
        );
    }

    /**
     * Assert that it's possible to create a new payment with metadata on it.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCreatePaymentWithMetadata(): void
    {
        $orderLines = new OrderLineCollection(
            data: [
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
            ]
        );
        $metadata = new Metadata(
            custom: new Metadata\EntryCollection(
                data: [
                    new Metadata\Entry(
                        key: 'foo',
                        value: 'bar'
                    ),
                    new Metadata\Entry(
                        key: 'fnord',
                        value: 'baz'
                    )
                ]
            )
        );

        $createdOrder = Repository::create(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: $orderLines,
            metadata: $metadata
        );

        if (!isset($createdOrder->order->actionLog[0])) {
            throw new MissingKeyException(message: 'actionLog contains no entries');
        }

        $createdMetadata = $createdOrder->metadata;

        $this->assertEqualsCanonicalizing(
            expected: $metadata->custom->toArray(),
            actual: $createdMetadata->custom !== null ? $createdMetadata->custom->toArray() : []
        );
    }
}
