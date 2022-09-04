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
use Resursbank\Ecom\Lib\Validation\FloatValidation;

/**
 * Test float validation methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FloatValidationTest extends TestCase
{
    /**
     * @var FloatValidation
     */
    private FloatValidation $floatValidation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->floatValidation = new FloatValidation();

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
        $this->floatValidation->getKey(data: ['Finland', 'Brown'], key: 'win');
    }

    /**
     * Assert getKey() throws IllegalTypeException when the needle exists but
     * is not a float.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithIllegalType(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->floatValidation->getKey(data: ['mime' => true], key: 'mime');
    }

    /**
     * Assert getKey() return validated float value.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyReturnsFloat(): void
    {
        self::assertSame(
            expected: 10.55,
            actual: $this->floatValidation->getKey(
                data: ['epic' => 10.55],
                key: 'epic'
            )
        );
    }/**
 * Assert isPositive() throws IllegalValueException when the value is
 * negative.
 *
 * @return void
 * @throws IllegalValueException
 */
    public function testIsPositiveThrowsOnNegative(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->floatValidation->isPositive(value: -1);
    }

    /**
     * Assert isPositive() returns true when the value is positive.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsPositiveReturnTrue(): void
    {
        self::assertTrue(
            condition: $this->floatValidation->isPositive(value: 1)
        );
    }
}
