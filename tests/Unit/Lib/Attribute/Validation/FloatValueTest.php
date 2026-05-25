<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Attribute\Validation;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\FloatValue;

/**
 * Unit tests for the FloatValue validation attribute.
 */
#[AllowMockObjectsWithoutExpectations]
class FloatValueTest extends TestCase
{
    /**
     * Confirm that an AttributeParameterException is thrown for min > max.
     *
     * @throws AttributeParameterException
     */
    public function testInvalidMinMaxParameters(): void
    {
        $this->expectException(exception: AttributeParameterException::class);
        new FloatValue(min: 10.1, max: 9.2);
    }

    /**
     * Validate the output of getAcceptedValues.
     *
     * @throws AttributeParameterException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetAcceptedValues(): void
    {
        $min = 5.552;
        $max = 200.101;
        $object = new FloatValue(min: $min, max: $max, scale: 3);
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $object->getAcceptedValues(
                parameter: $reflectionParameter,
                size: 1000
            ) as $value
        ) {
            if ($value >= $min && $value <= $max) {
                $this->addToAssertionCount(count: 1);
            } else {
                $this->fail(message: $value . ' is outside configured range!');
            }
        }
    }

    /**
     * Validate the output of getRejectedValues.
     *
     * @throws AttributeParameterException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetRejectedValues(): void
    {
        $min = 32.32;
        $max = 122.12;
        $object = new FloatValue(min: $min, max: $max);
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $object->getRejectedValues(
                parameter: $reflectionParameter,
                size: 1000
            ) as $value
        ) {
            if ($value < $min || $value > $max) {
                $this->addToAssertionCount(count: 1);
            } else {
                $this->fail(message: $value . ' is within configured range!');
            }
        }
    }

    /**
     * Assert that the scale parameter has a minimum value of 1.
     *
     * @throws AttributeParameterException
     */
    public function testScale(): void
    {
        // Assert we accept at least 1 as scale.
        new FloatValue(scale: 1);
        $this->addToAssertionCount(count: 1);

        // Assert we do accept great values as scale.
        new FloatValue(scale: 100);
        $this->addToAssertionCount(count: 1);

        // Assert we do not accept 0 as scale.
        try {
            new FloatValue(scale: 0);
        } catch (AttributeParameterException) {
            $this->addToAssertionCount(count: 1);
        }

        // Assert we do not accept negative values as scale.
        $this->expectException(AttributeParameterException::class);
        new FloatValue(scale: -1);
    }

    /**
     * Assert that the getNumberOfDecimals method works as expected.
     *
     * @throws AttributeParameterException
     */
    public function testGetNumberOfDecimals(): void
    {
        $object = new FloatValue(scale: 2);

        $this->assertEquals(
            expected: 2,
            actual: $object->getNumberOfDecimals(value: 1.23)
        );

        $this->assertEquals(
            expected: 0,
            actual: $object->getNumberOfDecimals(value: 1)
        );
    }

    /**
     * Assert that the validate method works as expected.
     *
     * @throws IllegalValueException|AttributeParameterException
     */
    public function testValidate(): void
    {
        $object = new FloatValue(min: 1.0, max: 10.0, scale: 2);

        // Assert that the value is within the configured range.
        $object->validate(name: 'test', value: 5.55);
        $this->addToAssertionCount(count: 1);

        // Assert that the value is below the configured minimum.
        try {
            $object->validate(name: 'test', value: 0.99);
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        // Assert that the value is above the configured maximum.
        try {
            $object->validate(name: 'test', value: 10.01);
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        // Assert that the value contains more decimals than the configured scale.
        $this->expectException(exception: IllegalValueException::class);
        $object->validate(name: 'test', value: 5.555);
    }

    /**
     * Assert the getRandomValue method works as expected.
     */
    public function testGetRandomValue(): void
    {
        $object = new FloatValue(min: -594.12, max: 99999.99, scale: 2);

        // Test 1000 random values.
        for ($i = 0; $i < 1000; $i++) {
            $value = $object->getRandomValue();
            $this->assertGreaterThanOrEqual(-594.12, $value);
            $this->assertLessThanOrEqual(99999.99, $value);
        }
    }

    /**
     * Assert that the getNumberOfDecimals method works as expected.
     *
     * @throws AttributeParameterException
     */
    public function testGetDecimalValue(): void
    {
        $object = new FloatValue(scale: 2);

        $this->assertEquals(
            expected: 23,
            actual: $object->getDecimalValue(value: 1.23)
        );

        $this->assertEquals(
            expected: 0,
            actual: $object->getNumberOfDecimals(value: 1)
        );
    }
}
