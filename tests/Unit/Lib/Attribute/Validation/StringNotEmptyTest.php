<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Attribute\Validation;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;

/**
 * Unit tests for the StringNotEmpty validation attribute.
 */
class StringNotEmptyTest extends TestCase
{
    /**
     * Validate the output of getAcceptedValues.
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetAcceptedValues(): void
    {
        $obj = new StringNotEmpty();
        $reflectionParameter = $this->createMock(
            originalClassName: ReflectionParameter::class
        );

        foreach (
            $obj->getAcceptedValues(
                parameter: $reflectionParameter,
                size: 10
            ) as $value
        ) {
            if (!empty($value)) {
                $this->addToAssertionCount(count: 1);
            } else {
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
        $obj = new StringNotEmpty();
        $reflectionParameter = $this->createMock(
            originalClassName: ReflectionParameter::class
        );

        foreach (
            $obj->getRejectedValues(
                parameter: $reflectionParameter,
                size: 10
            ) as $value
        ) {
            if (empty($value)) {
                $this->addToAssertionCount(count: 1);
            } else {
                $this->fail(message: 'Value is not empty');
            }
        }
    }
}
