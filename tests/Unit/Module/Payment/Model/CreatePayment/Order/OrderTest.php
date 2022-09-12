<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Model\CreatePayment\Order;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Order;
use Resursbank\Ecom\Module\Payment\Models\Order\OrderLine;
use Resursbank\Ecom\Module\Payment\Models\Order\OrderLineCollection;

/**
 * Test data integrity of order entity model.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OrderTest extends TestCase
{
    private static array $data = [];
    private static OrderLine $orderLine;

    /**
     * @return void
     * @throws JsonException
     * @throws IllegalValueException
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     */
    protected function setUp(): void
    {
        self::$orderLine = new OrderLine(
            description: 'Item',
            quantity: 1,
            reference: 'I-200',
            type: OrderLineType::NORMAL,
            quantityUnit: 'st',
            unitAmountIncludingVat: 10,
            totalAmountIncludingVat: 11,
            totalVatAmount: 1,
            vatRate: 10,
        );

        /** @var array $data */
        $data = json_decode(json_encode(new Order(
            orderLines: new OrderLineCollection(
                data: [self::$orderLine],
            ),
            orderReference: 'asdf'
        ), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        self::$data = $data;

        parent::setUp();
    }

    /**
     * @param array $updates
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    private function convert(
        array $updates = []
    ): void {
        $result = null;

        try {
            $result = DataConverter::stdClassToType(
                object: (object) array_merge(self::$data, $updates),
                type: Order::class
            );
        } catch (Exception $e) {
            $asd = 123;
        }

        if (!$result instanceof Order) {
            throw new TestException(
                message: 'Failed to convert stdClass to PaymentMethod.'
            );
        }
    }

    /**
     * Assert validateDescription() throws IllegalValueException when its
     * length is too long.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     */
    public function testValidateOrderLinesThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    0,
                    1001,
                    self::$orderLine,
                )
            )
        );
    }

    /**
     * Assert validateOrderReference() throws IllegalValueException when it's
     * empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     */
    public function testValidateOrderReferenceThrowsWhenEmpty(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    0,
                    5,
                    self::$orderLine,
                ),
            ),
            orderReference: ''
        );
    }

    /**
     * Assert validateOrderReference() throws IllegalValueException when it's
     * too long.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     */
    public function testValidateOrderReferenceThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    0,
                    5,
                    self::$orderLine,
                ),
            ),
            orderReference: "asdf asdf asdf asdf asdf asdf asd"
        );
    }

    /**
     * Assert validateOrderReference() throws IllegalValueException when it's
     * using illegal characters.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     */
    public function testValidateOrderReferenceThrowsUsingIllegalCharacters(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new Order(
            orderLines: new OrderLineCollection(
                data: array_fill(
                    0,
                    5,
                    self::$orderLine,
                ),
            ),
            orderReference: 'äåö'
        );
    }
}
