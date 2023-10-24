<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\Ecom\Lib\Utilities\Random\DataType;
use stdClass;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function strlen;

/**
 * Unit tests for Random.
 */
// phpcs:disable SlevomatCodingStandard.Complexity.Cognitive
class RandomTest extends TestCase
{
    /**
     * Convert variable/object type to DataType value.
     *
     * @throws IllegalTypeException
     */
    private function realTypeToDataType(mixed $value): DataType
    {
        $return = null;

        if (is_string(value: $value)) {
            $return = DataType::STRING;
        }

        if (is_int(value: $value)) {
            $return = DataType::INT;
        }

        if (is_float(value: $value)) {
            $return = DataType::FLOAT;
        }

        if (is_bool(value: $value)) {
            $return = DataType::BOOL;
        }

        if ($value instanceof stdClass) {
            $return = DataType::OBJECT;
        }

        if (is_array(value: $value)) {
            $return = DataType::ARRAY;
        }

        if ($return === null) {
            throw new IllegalTypeException(
                message: 'Supplied value of type not present in DataType'
            );
        }

        return $return;
    }

    /**
     * Validate getTypeValue method output.
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testGetTypeValue(): void
    {
        foreach (DataType::cases() as $case) {
            $value = Random::getTypeValue(type: $case);

            if ($case === DataType::STRING) {
                if (is_string(value: $value)) {
                    $this->addToAssertionCount(count: 1);
                } else {
                    $this->fail(message: 'Value is not of type string');
                }
            }

            if ($case === DataType::INT) {
                if (is_int(value: $value)) {
                    $this->addToAssertionCount(count: 1);
                } else {
                    $this->fail(message: 'Value is not of type int');
                }
            }

            if ($case === DataType::FLOAT) {
                if (is_float(value: $value)) {
                    $this->addToAssertionCount(count: 1);
                } else {
                    $this->fail(message: 'Value is not of type float');
                }
            }

            if ($case === DataType::BOOL) {
                if (is_bool(value: $value)) {
                    $this->addToAssertionCount(count: 1);
                } else {
                    $this->fail(message: 'Value is not of type bool');
                }
            }

            if ($case === DataType::OBJECT) {
                if ($value instanceof stdClass) {
                    $this->addToAssertionCount(count: 1);
                } else {
                    $this->fail(message: 'Value is not of type stdClass');
                }
            }

            if ($case !== DataType::ARRAY) {
                continue;
            }

            if (is_array(value: $value)) {
                $this->addToAssertionCount(count: 1);
            } else {
                $this->fail(message: 'Value is not of type array');
            }
        }
    }

    /**
     * Validate getType output.
     */
    public function testGetType(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertContains(
                needle: Random::getType(),
                haystack: DataType::cases(),
                message: 'Returned type not present in DataType enum'
            );
        }
    }

    /**
     * Validate getValue output.
     *
     * @throws Exception
     */
    public function testGetValue(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $value = Random::getValue();

            try {
                $this->realTypeToDataType(value: $value);
                $this->addToAssertionCount(count: 1);
            } catch (IllegalTypeException) {
                $this->fail(
                    message: 'Received value of type not present in DataType'
                );
            }
        }
    }

    /**
     * Validate getString output.
     *
     * @throws Exception
     */
    public function testGetString(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $length = random_int(min: 1, max: 9999);
            $value = Random::getString(length: $length);

            $this->assertIsString(actual: $value);
            $this->assertEquals(
                expected: $length,
                actual: strlen(string: $value)
            );
        }
    }

    /**
     * Validate getInt output.
     *
     * @throws Exception
     */
    public function testGetInt(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $min = random_int(min: 0, max: 9999999);
            $max = random_int(min: $min, max: 9999999);
            $value = Random::getInt(min: $min, max: $max);

            $this->assertIsInt(actual: $value);
            $this->assertGreaterThan(expected: $min - 1, actual: $value);
            $this->assertLessThan(expected: $max + 1, actual: $value);
        }
    }

    /**
     * Validate getBool output.
     *
     * @throws Exception
     */
    public function testGetBool(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $value = Random::getBool();

            $this->assertIsBool(actual: $value);
        }
    }

    /**
     * Validate getObject output.
     */
    public function testGetObject(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $value = Random::getObject();

            $this->assertInstanceOf(expected: stdClass::class, actual: $value);
        }
    }

    /**
     * Validate getArray output.
     *
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testGetArray(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $size = random_int(min: 1, max: 99);
            /* @phpstan-ignore-next-line */
            $type = DataType::cases()[array_rand(array: DataType::cases())];
            $value = Random::getArray(size: $size, type: $type);

            $this->assertIsArray(actual: $value);
            $this->assertCount(expectedCount: $size, haystack: $value);

            foreach ($value as $item) {
                $this->assertEquals(
                    expected: $type,
                    actual: $this->realTypeToDataType(value: $item)
                );
            }
        }
    }
}
