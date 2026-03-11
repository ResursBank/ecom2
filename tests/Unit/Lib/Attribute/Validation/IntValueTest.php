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
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;

/**
 * Unit tests for the IntValue validation attribute.
 */
#[AllowMockObjectsWithoutExpectations]
class IntValueTest extends TestCase
{
    /**
     * Confirm that an AttributeParameterException is thrown for min > max.
     *
     * @throws AttributeParameterException
     */
    public function testInvalidMinMaxParameters(): void
    {
        $this->expectException(exception: AttributeParameterException::class);
        new IntValue(min: 10, max: 9);
    }

    /**
     * Validate the output of getAcceptedValues.
     *
     * @throws AttributeParameterException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetAcceptedValues(): void
    {
        $min = 5;
        $max = 200;
        $object = new IntValue(min: $min, max: $max);
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
                $this->fail(message: 'Value is outside of configured range!');
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
        $min = 5;
        $max = 200;
        $object = new IntValue(min: $min, max: $max);
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
                $this->fail(message: 'Value is within configured range!');
            }
        }
    }

    /**
     * Assert that the min and max parameters cannot both be null.
     */
    public function testNullMinMaxParameters(): void
    {
        $this->expectException(exception: AttributeParameterException::class);
        new IntValue(min: null, max: null);
    }
}
