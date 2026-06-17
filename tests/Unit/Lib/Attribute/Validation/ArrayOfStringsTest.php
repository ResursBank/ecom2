<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Attribute\Validation;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;

/**
 * Unit tests for ArrayOfStrings.
 */
#[AllowMockObjectsWithoutExpectations]
class ArrayOfStringsTest extends TestCase
{
    /**
     * Verify validate throws IllegalTypeException for non-string elements.
     *
     * @throws IllegalTypeException
     */
    public function testValidateWithNonStringElements(): void
    {
        $name = 'foobar';
        $value = [
            'foo',
            'bar',
            42,
            'baf',
            'baz'
        ];

        $validator = new ArrayOfStrings();
        $this->expectException(IllegalTypeException::class);
        $validator->validate(name: $name, value: $value);
    }

    /**
     * Verify first element is empty if getSizeAttribute returns null.
     *
     * @throws Exception
     * @throws AttributeParameterException
     */
    public function testGetRejectedValuesWithNoMinLength(): void
    {
        $mockedValidator = $this->createPartialMock(
            type: ArrayOfStrings::class,
            methods: ['getSizeAttribute']
        );
        $mockedValidator->method('getSizeAttribute')
            ->willReturnOnConsecutiveCalls(
                null,
                new ArraySize(min: 0, max: 10)
            );

        $mockedParameter = $this->createPartialMock(
            type: ReflectionParameter::class,
            methods: ['getAttributes']
        );

        $mockedParameter->method(constraint: 'getAttributes')
            ->willReturn(value: []);

        $values = $mockedValidator->getRejectedValues(
            parameter: $mockedParameter,
            size: 10
        );

        $this->assertCount(expectedCount: 10, haystack: $values);

        $this->assertEmpty(actual: $values[0]);
    }
}
