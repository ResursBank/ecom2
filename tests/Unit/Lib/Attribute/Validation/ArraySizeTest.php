<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Attribute\Validation;

use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Throwable;

/**
 * Unit tests for the ArraySize validation attribute.
 */
class ArraySizeTest extends TestCase
{
    /**
     * Confirm that an AttributeParameterException is thrown for min > max.
     *
     * @throws AttributeParameterException
     */
    public function testMinLargerThanMax(): void
    {
        $this->expectException(exception: AttributeParameterException::class);
        new ArraySize(min: 10, max: 5);
    }

    /**
     * Confirm that an AttributeParameterException is thrown for negative min.
     *
     * @throws AttributeParameterException
     */
    public function testNegativeLimits(): void
    {
        $this->expectException(exception: AttributeParameterException::class);
        new ArraySize(min: -2, max: -1);
    }

    /**
     * Validate the output of getAcceptedValues.
     *
     * @throws AttributeParameterException
     * @throws Throwable
     */
    public function testGetAcceptedValues(): void
    {
        $min = 5;
        $max = 200;
        $object = new ArraySize(min: $min, max: $max);
        $reflectionParameter = $this->createMock(
            originalClassName: ReflectionParameter::class
        );

        foreach (
            $object->getAcceptedValues(
                parameter: $reflectionParameter,
                size: 100
            ) as $value
        ) {
            if (count($value) >= $min && count($value) <= $max) {
                $this->addToAssertionCount(count: 1);
                continue;
            }

            $this->fail(message: 'Value is outside of configured range!');
        }
    }

    /**
     * Validate the output of getRejectedValues.
     *
     * @throws AttributeParameterException
     * @throws Throwable
     */
    public function testGetRejectedValues(): void
    {
        $min = 5;
        $max = 200;
        $object = new ArraySize(min: $min, max: $max);
        $reflectionParameter = $this->createMock(
            originalClassName: ReflectionParameter::class
        );

        foreach (
            $object->getRejectedValues(
                parameter: $reflectionParameter,
                size: 100
            ) as $value
        ) {
            if (count($value) < $min || count($value) > $max) {
                $this->addToAssertionCount(count: 1);
                continue;
            }

            $this->fail(message: 'Value is within configured range!');
        }
    }
}
