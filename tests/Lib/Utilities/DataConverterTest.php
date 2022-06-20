<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Utilities;

use ArgumentCountError;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\DataConverter\TestClasses;
use stdClass;

/**
 * Verifies that the DataConverter class works as intended.
 *
 *  @psalm-suppress PropertyNotSetInConstructor
 */
final class DataConverterTest extends TestCase
{
    /**
     * Verify that the stdClass converter is able to convert object containing simple scalar types
     *
     * @return void
     * @throws ReflectionException
     */
    public function testSimpleConversion(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->message = 'Foobar';

        $expected = new TestClasses\SimpleDummy(
            int: 42,
            message: 'Foobar'
        );

        $output = DataConverter::stdClassToType(object: $data, type: TestClasses\SimpleDummy::class);

        $this::assertEquals(
            expected: $expected,
            actual: $output
        );
    }

    /**
     * Verify that the stdClass converter properly converts arrays to arrays
     *
     * @return void
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

        $output = DataConverter::stdClassToType(object: $data, type: TestClasses\ArrayDummy::class);

        $this::assertEquals(
            expected: $expected,
            actual: $output
        );
    }

    /**
     * Verify that the stdClass converter can handle conversion of objects within objects
     *
     * @return void
     * @throws ReflectionException
     */
    public function testConvertObjectContainingObject(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->simpleDummy = new stdClass();
        $data->simpleDummy->int = 127;
        $data->simpleDummy->message = 'Foo';

        $expected = new TestClasses\ComplexDummy(
            int: 42,
            simpleDummy: new TestClasses\SimpleDummy(
                int: 127,
                message: 'Foo'
            )
        );

        $output = DataConverter::stdClassToType(object: $data, type: TestClasses\ComplexDummy::class);

        $this::assertEquals(
            expected: $expected,
            actual: $output
        );
    }

    /**
     * Verify that the stdClass converter doesn't fail when original stdClass object has extra properties but instead
     * quietly removes them.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testConvertObjectWithExtraProperties(): void
    {
        $data = new stdClass();
        $data->int = 42;
        $data->message = 'Foobar';
        $data->other = 'baz';

        $expected = new TestClasses\SimpleDummy(
            int: 42,
            message: 'Foobar'
        );

        $output = DataConverter::stdClassToType(object: $data, type: TestClasses\SimpleDummy::class);

        $this::assertEquals(
            expected: $expected,
            actual: $output
        );
    }

    /**
     * Verify that if there are missing properties the stdClass converter will throw the appropriate exception.
     *
     * @return void
     * @throws ArgumentCountError
     * @throws ReflectionException
     */
    public function testConvertObjectWithMissingProperties(): void
    {
        $data = new stdClass();
        $data->int = 42;

        $this->expectException(exception: ArgumentCountError::class);
        DataConverter::stdClassToType(object: $data, type: TestClasses\SimpleDummy::class);
    }
}
