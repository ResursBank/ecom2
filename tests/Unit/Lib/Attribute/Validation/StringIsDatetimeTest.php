<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Attribute\Validation;

use Exception;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;

/**
 * Unit tests for the StringIsDatetime validation attribute.
 */
#[AllowMockObjectsWithoutExpectations]
class StringIsDatetimeTest extends TestCase
{
    /**
     * Validate the output of getAcceptedValues.
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetAcceptedValues(): void
    {
        $obj = new StringIsDatetime();
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $obj->getAcceptedValues(
                parameter: $reflectionParameter,
                size: 10
            ) as $value
        ) {
            try {
                $obj->validate(name: $value, value: $value);
                $this->addToAssertionCount(count: 1);
            } catch (IllegalValueException) {
                $this->fail(message: 'Value is empty');
            }
        }
    }

    /**
     * Validate the output of getRejectedValues.
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetRejectedValues(): void
    {
        $obj = new StringIsDateTime();
        $reflectionParameter = $this->createMock(
            type: ReflectionParameter::class
        );

        foreach (
            $obj->getRejectedValues(
                parameter: $reflectionParameter,
                size: 10
            ) as $value
        ) {
            try {
                $obj->validate(name: $value, value: $value);
                $this->fail(message: 'Value ' . $value . ' was not rejected');
            } catch (IllegalValueException) {
                $this->addToAssertionCount(count: 1);
            }
        }
    }
}
