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
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class IntValidationTest extends TestCase
{
    /**
     * @var IntValidation
     */
    private IntValidation $intValidation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->intValidation = new IntValidation();

        parent::setUp();
    }

    /**
     * Assert getKey() throws MissingKeyException when the needle does not
     * exist.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithMissing(): void
    {
        $this->expectException(exception: MissingKeyException::class);
        $this->intValidation->getKey(data: ['Sweden', 'Blue'], key: 'bacon');
    }

    /**
     * Assert getKey() throws IllegalTypeException when the needle exists but
     * is not an integer.
     *
     * @return void
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
     * @return void
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyReturnsInt(): void
    {
        self::assertSame(
            expected: 123,
            actual: $this->intValidation->getKey(
                data: ['epic' => 123],
                key: 'epic'
            )
        );
    }

    /**
     * Assert inRange() throws IllegalValueException when the tested integer is
     * out of range.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testInRangeThrowsWithIllegalValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->inRange(value: 100, min: 0, max: 1);
    }

    /**
     * Assert inRange() throws IllegalValueException when the max value is less
     * than the min value.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testInRangeThrowsWithIllegalValueWhenMaxIsInvalid(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->intValidation->inRange(value: 5, min: 3, max: 1);
    }

    /**
     * Asserts that inRange() validates that the tested integer within range.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testInRangeReturnsTrue(): void
    {
        self::assertTrue(
            condition: $this->intValidation->inRange(
                value: 5,
                min: 0,
                max: 10
            )
        );
    }
}
