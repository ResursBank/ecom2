<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use ArgumentCountError;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\EcomTest\Data\DataConverter as TestClasses;
use stdClass;

/**
 * Verifies that the DataConverter class works as intended.
 */
final class DataConverterTest extends TestCase
{
    /**
     * Verify that the stdClass converter is able to convert object containing simple scalar types
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public function testSimpleConversion(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->message = 'Foobar';

        $expected = new TestClasses\SimpleDummy(int: 42, message: 'Foobar');

        $output = DataConverter::stdClassToType(
            object: $data,
            type: TestClasses\SimpleDummy::class
        );

        $this::assertEquals(expected: $expected, actual: $output);
    }

    /**
     * Verify that the stdClass converter properly converts arrays to arrays
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public function testConvertWithArrays(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->arr = [1, 2, 3];

        $expected = new TestClasses\ArrayDummy(
            int: 42,
            arr: [1, 2, 3]
        );

        $output = DataConverter::stdClassToType(
            object: $data,
            type: TestClasses\ArrayDummy::class
        );

        $this::assertEquals(expected: $expected, actual: $output);
    }

    /**
     * Verify that the stdClass converter can handle conversion of objects within objects
     *
     * @throws ReflectionException
     * @throws IllegalTypeException
     */
    public function testConvertObjectContainingObject(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->simpleDummy = new stdClass();
        $data->simpleDummy->int = 127;
        $data->simpleDummy->message = 'Foo';
        $data->simpleDummyCollection = [
            (object)[
                'int' => 31,
                'message' => 'Bar',
            ],
        ];

        $childDummy = new TestClasses\SimpleDummy(int: 31, message: 'Bar');
        $expected = new TestClasses\ComplexDummy(
            int: 42,
            simpleDummy: new TestClasses\SimpleDummy(
                int: 127,
                message: 'Foo'
            ),
            simpleDummyCollection: new TestClasses\SimpleDummyCollection(
                data: [$childDummy]
            )
        );

        $output = DataConverter::stdClassToType(
            object: $data,
            type: TestClasses\ComplexDummy::class
        );

        $this::assertEquals(expected: $expected, actual: $output);
    }

    /**
     * Verify that the stdClass converter doesn't fail when original stdClass object has extra properties but instead
     * quietly removes them.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public function testConvertObjectWithExtraProperties(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->message = 'Foobar';
        $data->other = 'baz';

        $expected = new TestClasses\SimpleDummy(int: 42, message: 'Foobar');

        $output = DataConverter::stdClassToType(
            object: $data,
            type: TestClasses\SimpleDummy::class
        );

        $this::assertEquals(expected: $expected, actual: $output);
    }

    /**
     * Verify that if there are missing properties the stdClass converter will throw the appropriate exception.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public function testConvertObjectWithMissingProperties(): void
    {
        $data = new stdClass();
        $data->int = 42;

        $this->expectException(exception: ArgumentCountError::class);
        DataConverter::stdClassToType(
            object: $data,
            type: TestClasses\SimpleDummy::class
        );
    }

    /**
     * Verify that missing nullable properties are mapped to null.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public function testConvertObjectWithMissingNullableProperty(): void
    {
        $data = new stdClass();
        $data->int = 42;

        $output = DataConverter::stdClassToType(
            object: $data,
            type: TestClasses\NullableDummy::class
        );

        $this::assertEquals(
            expected: new TestClasses\NullableDummy(int: 42, description: null),
            actual: $output
        );
    }

    /**
     * Verify real Cost model conversion when API omits nullable fields.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public function testConvertCostWithMissingNullableProperties(): void
    {
        $data = (object) [
            'name' => 'Plan 12m',
            'durationMonths' => 12,
            'setupFee' => 0.0,
            'totalCost' => 1200.0,
            'monthlyCost' => 100.0,
            'administrationFee' => 0.0,
            'effectiveInterest' => 0.0,
        ];

        /** @var Cost $output */
        $output = DataConverter::stdClassToType(
            object: $data,
            type: Cost::class
        );

        $this::assertInstanceOf(expected: Cost::class, actual: $output);
        $this::assertNull(actual: $output->description);
        $this::assertNull(actual: $output->interest);
    }
}
