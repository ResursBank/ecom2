<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment\Model;

require_once(__DIR__ . '/../../../../Data/OrderLine.php');

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\EcomTest\Data\OrderLine;
use Resursbank\Ecom\Module\Payment\Models\OrderLine as OrderLineModel;
use stdClass;

/**
 * Test data integrity of store entity model.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
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
                message: 'Conversion succeeded but did not return Method instance.'
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
     * value is too small.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenTooSmall(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => -10]);
    }

    /**
     * Assert validateVatRate() throws IllegalValueException when its
     * value is too big.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    public function testVatRateThrowsWhenTooBig(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['vatRate' => 110]);
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
        self::assertSame(
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
        self::assertSame(
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
        self::assertSame(
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
        self::assertSame(
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
        self::assertSame(
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
        self::assertSame(
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
        self::assertSame(
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
        self::assertSame(
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
        self::assertSame(
            expected: $this->data->totalVatAmount,
            actual: $this->item->totalVatAmount
        );
    }
}
