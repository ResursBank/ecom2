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
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;

use function strlen;

/**
 * Unit tests for the StringLength validation attribute.
 */
#[AllowMockObjectsWithoutExpectations]
class StringLengthTest extends TestCase
{
    /**
     * Confirm that an AttributeParameterException is thrown for negative min.
     *
     * @throws AttributeParameterException
     */
    public function testInvalidMinParameter(): void
    {
        $this->expectException(exception: AttributeParameterException::class);
        new StringLength(min: -1, max: 200);
    }

    /**
     * Confirm that an AttributeParameterException is thrown for min > max.
     *
     * @throws AttributeParameterException
     */
    public function testMinParameterGreaterThanMaxParameter(): void
    {
        $this->expectException(exception: AttributeParameterException::class);
        new StringLength(min: 100, max: 50);
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
        $object = new StringLength(min: $min, max: $max);
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $object->getAcceptedValues(
                parameter: $reflectionParameter,
                size: 100
            ) as $value
        ) {
            if (
                strlen(string: $value) >= $min &&
                strlen(string: $value) <= $max
            ) {
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
        $object = new StringLength(min: $min, max: $max);
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $object->getRejectedValues(
                parameter: $reflectionParameter,
                size: 100
            ) as $value
        ) {
            if (
                strlen(string: $value) < $min ||
                strlen(string: $value) > $max
            ) {
                $this->addToAssertionCount(count: 1);
            } else {
                $this->fail(message: 'Value is within configured range!');
            }
        }
    }
}
