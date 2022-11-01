<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment\Order\ActionLog;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine as OrderLineModel;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\EcomTest\Data\OrderLine;
use stdClass;

/**
 * Test data integrity of order line entity model.
 */
class OrderLineTest extends TestCase
{
    /**
     * @var OrderLineModel
     */
    private OrderLineModel $item;

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
        $this->data = OrderLine::getRandomData();

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
        /** @psalm-suppress MixedAssignment */
        foreach ($updates as $key => $val) {
            $this->data->{$key} = $val;
        }

        $item = DataConverter::stdClassToType(
            object: $this->data,
            type: OrderLineModel::class
        );

        if (!$item instanceof OrderLineModel) {
            throw new TestException(
                message: 'Conversion succeeded but did not return ' .
                    'Order Line instance.'
            );
        }

        $this->item = $item;
    }

    /**
     * Assert validateDescription() throws IllegalValueException when its
     * length is too long.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateDescriptionThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'description' => 'This text is way too long for this poor little ' .
                'model property.'
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
        $this->convert(updates: [
            'reference' => 'This text is way too long for this poor little ' .
                'model property.'
        ]);
    }

    /**
     * Assert validateQuantityUnit() throws IllegalValueException when its
     * length is too long.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testValidateQuantityUnitThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: [
            'quantityUnit' => 'This text is way too long for this poor little ' .
                'model property.'
        ]);
    }

    /**
     * Assert validateVatRate() throws IllegalValueException when its
     * value is negative.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => -10]);
    }

    /**
     * Assert validateVatRate() throws IllegalValueException when its
     * value has more than 2 decimals digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => 0.999]);
    }

    /**
     * Assert validateVatRate() throws IllegalValueException when its
     * value has more than 2 integer digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => 100]);
    }

    /**
     * Assert validateQuantity() throws IllegalValueException when its
     * value is negative.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityThrowsWhenNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['quantity' => -10]);
    }

    /**
     * Assert validateQuantity() throws IllegalValueException when its
     * value has more than 2 decimals digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['quantity' => 0.999]);
    }

    /**
     * Assert validateQuantity() throws IllegalValueException when its
     * value has more than 10 integer digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['quantity' => 99999999999]);
    }

    /**
     * Assert validateUnitAmountIncludingVat() throws IllegalValueException when
     * its value is negative.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testUnitAmountIncludingVatThrowsWhenNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['unitAmountIncludingVat' => -10]);
    }

    /**
     * Assert validateUnitAmountIncludingVat() throws IllegalValueException when
     * its value has more than 2 decimals digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testUnitAmountIncludingVatThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['unitAmountIncludingVat' => 0.999]);
    }

    /**
     * Assert validateUnitAmountIncludingVat() throws IllegalValueException when
     * its value has more than 10 integer digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testUnitAmountIncludingVatThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['unitAmountIncludingVat' => 99999999999]);
    }

    /**
     * Assert validateTotalAmountIncludingVat() throws IllegalValueException
     * when its value is negative.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalAmountIncludingVatThrowsWhenNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalAmountIncludingVat' => -10]);
    }

    /**
     * Assert validateTotalAmountIncludingVat() throws IllegalValueException
     * when its value has more than 2 decimals digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalAmountIncludingVatThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalAmountIncludingVat' => 0.999]);
    }

    /**
     * Assert validateTotalAmountIncludingVat() throws IllegalValueException
     * when its value has more than 10 integer digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalAmountIncludingVatThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalAmountIncludingVat' => 99999999999]);
    }

    /**
     * Assert validateTotalVatAmount() throws IllegalValueException when
     * its value is negative.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalVatAmountThrowsWhenNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalVatAmount' => -10]);
    }

    /**
     * Assert validateTotalVatAmount() throws IllegalValueException when
     * its value has more than 2 decimals digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalVatAmountThrowsWhenItHasTooManyDecimals(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalVatAmount' => 0.999]);
    }

    /**
     * Assert validateTotalVatAmount() throws IllegalValueException when
     * its value has more than 10 integer digits.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalVatAmountThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['totalVatAmount' => 99999999999]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testDescriptionWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->description,
            actual: $this->item->description
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testReferenceWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->reference,
            actual: $this->item->reference
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTypeWasAssigned(): void
    {
        $this->convert();
        $this->assertNotNull(actual: $this->item->type);
        $this->assertSame(
            expected: $this->data->type,
            actual: $this->item->type->value
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityUnitWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->quantityUnit,
            actual: $this->item->quantityUnit
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testQuantityWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->quantity,
            actual: $this->item->quantity
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->vatRate,
            actual: $this->item->vatRate
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testUnitAmountIncludingVatWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->unitAmountIncludingVat,
            actual: $this->item->unitAmountIncludingVat
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalAmountIncludingVatWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->totalAmountIncludingVat,
            actual: $this->item->totalAmountIncludingVat
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testTotalVatAmountWasAssigned(): void
    {
        $this->convert();
        $this->assertSame(
            expected: $this->data->totalVatAmount,
            actual: $this->item->totalVatAmount
        );
    }
}
