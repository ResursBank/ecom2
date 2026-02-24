<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\CreatePaymentRequest;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\OrderLineType;
use Resursbank\Ecom\Lib\Model\Payment\Order as OrderModel;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Enum\ActionType;
use Resursbank\EcomTest\Data\Order;
use stdClass;

use function array_fill;

/**
 * Test data integrity of order entity model.
 */
class OrderTest extends TestCase
{
    private stdClass $data;

    /**
     * @throws JsonException
     * @throws TestException
     */
    protected function setUp(): void
    {
        $this->data = Order::getData();

        parent::setUp();
    }

    /**
     * @param array<string, mixed> $updates
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    private function convert(
        array $updates = []
    ): void {
        foreach ($updates as $key => $val) {
            $this->data->{$key} = $val;
        }

        $item = DataConverter::stdClassToType(
            object: $this->data,
            type: OrderModel::class
        );

        if (!$item instanceof OrderModel) {
            throw new TestException(
                message: 'Conversion succeeded but did not return Order instance.'
            );
        }
    }

    /**
     * Assert validateOrderLines() throws error when its length is too long.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testValidateOrderLinesThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        $orderLines = new OrderModel\ActionLog\OrderLineCollection(
            data: array_fill(
                start_index: 0,
                count: 1001,
                value: new OrderLine(
                    quantity: 20.1,
                    quantityUnit: 'test',
                    vatRate: 20,
                    totalAmountIncludingVat: 20.1,
                    description: 'test',
                    reference: 'test',
                    type: OrderLineType::NORMAL,
                    unitAmountIncludingVat: 20,
                    totalVatAmount: 20.1
                )
            )
        );

        new OrderModel\ActionLog(
            actionId: '160d2b10-7586-4a32-87a1-23a425b252ce',
            type: ActionType::CREATE,
            created: '2022-09-07T14:52:56.709',
            orderLines: $orderLines,
            creator: 'jultomten'
        );
    }

    /**
     * Assert validateOrderLines() throws error when its length is too short.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    public function testValidateOrderLinesThrowsWhenTooShort(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new OrderModel\ActionLog(
            actionId: '160d2b10-7586-4a32-87a1-23a425b252ce',
            type: ActionType::CREATE,
            created: '2022-09-07T14:52:56.709',
            orderLines: new OrderModel\ActionLog\OrderLineCollection(data: []),
            creator: 'jultomten'
        );
    }

    /**
     * Assert validateReference() throws error when reference is too long.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderReference' => 'Lorem ipsum dolor sit amet, consectetur ' .
                'adipiscing elit. Pellentesque tempus gravida varius.',
        ]);
    }

    /**
     * Assert validateReference() throws error when reference too short.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenTooShort(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderReference' => '',
        ]);
    }

    /**
     * Assert validateReference() throws error on illegal characters.
     *
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenUsingIllegalChars(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderReference' => "·n”“©»ðßøæ£¡@¡\]£¡\¶\}t!",
        ]);
    }
}
