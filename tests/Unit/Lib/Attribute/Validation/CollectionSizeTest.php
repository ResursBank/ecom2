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
use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Throwable;

/**
 * Unit tests for the CollectionSze validation attribute.
 */
#[AllowMockObjectsWithoutExpectations]
class CollectionSizeTest extends TestCase
{
    /**
     * Validate the output of getAcceptedValues.
     *
     * @throws AttributeParameterException
     * @throws Throwable
     */
    public function testGetAcceptedValues(): void
    {
        $min = 5;
        $max = 50;
        $object = new CollectionSize(min: $min, max: $max);
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $object->getAcceptedValues(
                parameter: $reflectionParameter,
                size: 50
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
        $max = 50;
        $object = new CollectionSize(min: $min, max: $max);
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $object->getRejectedValues(
                parameter: $reflectionParameter,
                size: 50
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
