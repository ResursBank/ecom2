<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Validation;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Lib\Validation\IntValidation;

/**
 * Test integer validation methods.
 */
final class IntValidationTest extends TestCase
{
    private IntValidation $intValidation;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->intValidation = new IntValidation();

        parent::setUp();
    }

    /**
     * Assert getKey() throws MissingKeyException if needle doesn't exist.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithMissing(): void
    {
        $this->expectException(exception: MissingKeyException::class);
        $this->intValidation->getKey(data: ['Sweden', 'Blue'], key: 'bacon');
    }

    /**
     * Assert getKey() throws IllegalTypeException for non-integer needle.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithIllegalType(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->intValidation->getKey(data: ['epic' => '999'], key: 'epic');
    }

    /**
     * Assert getKey() return validated integer value.
     *
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyReturnsInt(): void
    {
        $this->assertSame(
            expected: 123,
            actual: $this->intValidation->getKey(
                data: ['epic' => 123],
                key: 'epic'
            )
        );
    }

    /**
     * Assert isPositive() throws IllegalValueException if value is negative.
     *
     * @throws IllegalValueException
     */
    public function testIsPositiveThrowsOnNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->isPositive(value: -1);
    }

    /**
     * Assert isPositive() returns true when the value is positive.
     */
    public function testInRangeThrowsWithIllegalValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->inRange(value: 100, min: 0, max: 1);
    }

    /**
     * Assert inRange() throws IllegalValueException if max > min.
     *
     * @throws IllegalValueException
     */
    public function testIsPositiveReturnTrue(): void
    {
        $this->assertTrue(
            condition: $this->intValidation->isPositive(value: 1)
        );
    }

    /**
     * Assert isGreaterThan() throws IllegalValueException if value < min.
     *
     * @throws IllegalValueException
     */
    public function testIsGreaterThanThrowsOnLessThan(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->isGreaterThan(value: 1, min: 2);
    }

    /**
     * Assert isGreaterThan() returns true when value greater than min.
     *
     * @throws IllegalValueException
     */
    public function testIsGreaterThanReturnTrue(): void
    {
        $this->assertTrue(
            condition: $this->intValidation->isGreaterThan(value: 2, min: 1)
        );
    }

    /**
     * Assert inRange() throws IllegalValueException for out-of-range integer.
     */
    public function testInRangeThrowsWithIllegalValueWhenMaxIsInvalid(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->inRange(value: 5, min: 3, max: 1);
    }

    /**
     * Asserts that inRange() validates that the tested integer within range.
     *
     * @throws IllegalValueException
     */
    public function testInRangeReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->intValidation->inRange(
                value: 5,
                min: 0,
                max: 10
            )
        );
    }
}
