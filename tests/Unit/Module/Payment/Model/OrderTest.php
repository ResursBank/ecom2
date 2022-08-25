<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Model;

require_once(__DIR__ . '/../../../../Data/Order.php');

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Payment\Models\OrderLine;
use Resursbank\EcomTest\Data\Order;
use Resursbank\Ecom\Module\Payment\Models\Order as OrderModel;
use stdClass;

/**
 * Test data integrity of store entity model.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OrderTest extends TestCase
{
    /**
     * @var OrderModel
     */
    private OrderModel $item;

    /**
     * @var stdClass
     */
    private stdClass $data;

    /**
     * @return void
     * @throws JsonException
     * @throws TestException
     */
    protected function setUp(): void
    {
        $this->data = Order::getData();

        parent::setUp();
    }

    /**
     * @param array $updates
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function convert(
        array $updates = []
    ): void {
        /** @psalm-suppress MixedAssignment */
        foreach ($updates as $key => $val) {
            $this->data->{$key} = $val;
        }

        if (\array_key_exists('orderReference', $updates)) {
            $orderLine = $this->data->orderLines[0];
            $arr = (array) $orderLine;
            $arr['type'] = OrderLineType::NORMAL;
            $orderLineInstance = new OrderLine(...$arr);
            $stop = 123;
        }

        $item = DataConverter::stdClassToType(
            object: $this->data,
            type: OrderModel::class
        );

        if (!$item instanceof OrderModel) {
            throw new TestException(
                message: 'Conversion succeeded but did not return Method instance.'
            );
        }

        $this->item = $item;
    }

    /**
     * Assert validateOrderLines() throws IllegalValueException when its
     * length is too long.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateOrderLinesThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'orderLines' => \array_fill(
                0,
                1001,
                new OrderLine(
                    'test',
                    'test',
                    OrderLineType::NORMAL,
                    'test',
                    20,
                    20,
                    20.1,
                    20.1,
                    20.1
                )
            )
        ]);
    }

    /**
     * Assert validateOrderLines() throws IllegalValueException when its
     * length is too short.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateOrderLinesThrowsWhenTooShort(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert([
            'orderLines' => []
        ]);
    }

    /**
     * Assert validateReference() throws IllegalValueException when its
     * length is too long.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert([
            'orderReference' => 'Lorem ipsum dolor sit amet, consectetur ' .
                'adipiscing elit. Pellentesque tempus gravida varius.'
        ]);
    }

    /**
     * Assert validateReference() throws IllegalValueException when its
     * length is too short.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenTooShort(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert([
            'orderReference' => ''
        ]);
    }

    /**
     * Assert validateReference() throws IllegalValueException when it uses
     * illegal characters.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateReferenceThrowsWhenUsingIllegalChars(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert([
            'orderReference' => 'Test!'
        ]);
    }

//    /**
//     * Assert validateReference() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateReferenceThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'reference' => 'This text is way too long for this poor little ' .
//                'model property.'
//        ]);
//    }
//
//    /**
//     * Assert validateQuantityUnit() throws IllegalValueException when its
//     * length is too long.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testValidateQuantityUnitThrowsWhenTooLong(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: [
//            'quantityUnit' => 'This text is way too long for this poor little ' .
//                'model property.'
//        ]);
//    }
//
//    /**
//     * Assert validateVatRate() throws IllegalValueException when its
//     * value is too small.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testVatRateThrowsWhenTooSmall(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: ['vatRate' => -10]);
//    }
//
//    /**
//     * Assert validateVatRate() throws IllegalValueException when its
//     * value is too big.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testVatRateThrowsWhenTooBig(): void
//    {
//        $this->expectException(exception: IllegalValueException::class);
//        $this->convert(updates: ['vatRate' => 110]);
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testDescriptionWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->description,
//            actual: $this->item->description
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testReferenceWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->reference,
//            actual: $this->item->reference
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testTypeWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->type,
//            actual: $this->item->type->value
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testQuantityUnitWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->quantityUnit,
//            actual: $this->item->quantityUnit
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testQuantityWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->quantity,
//            actual: $this->item->quantity
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testVatRateWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->vatRate,
//            actual: $this->item->vatRate
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testUnitAmountIncludingVatWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->unitAmountIncludingVat,
//            actual: $this->item->unitAmountIncludingVat
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testTotalAmountIncludingVatWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->totalAmountIncludingVat,
//            actual: $this->item->totalAmountIncludingVat
//        );
//    }
//
//    /**
//     * Assert property was assigned during object conversion.
//     *
//     * @return void
//     * @throws ReflectionException
//     * @throws TestException
//     * @throws IllegalTypeException
//     */
//    public function testTotalVatAmountWasAssigned(): void
//    {
//        $this->convert();
//        self::assertSame(
//            expected: $this->data->totalVatAmount,
//            actual: $this->item->totalVatAmount
//        );
//    }
}
