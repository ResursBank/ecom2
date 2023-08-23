<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Checkbox;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Rco\Checkbox.
 */
class CheckboxTest extends TestCase
{
    /**
     * Verify that an empty id string is not accepted.
     *
     * @throws EmptyValueException
     * @throws Exception
     */
    public function testEmptyId(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Checkbox(
            id: '',
            label: Strings::generateRandomString(length: 12)
        );
    }

    /**
     * Verify that a too long id string is not accepted.
     *
     * @throws EmptyValueException
     * @throws Exception
     */
    public function testTooLongId(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Checkbox(
            id: Strings::generateRandomString(length: 33),
            label: Strings::generateRandomString(length: 12)
        );
    }

    /**
     * Verify that id strings of allowed lengths do not cause exception to be thrown.
     *
     * @throws EmptyValueException
     */
    public function testValidIds(): void
    {
        $min = Strings::generateRandomString(length: 1);
        $max = Strings::generateRandomString(length: 32);

        $minCheckbox = new Checkbox(
            id: $min,
            label: Strings::generateRandomString(length: 12)
        );
        $maxCheckbox = new Checkbox(
            id: $max,
            label: Strings::generateRandomString(length: 12)
        );

        $this->assertEquals(expected: $min, actual: $minCheckbox->id);
        $this->assertEquals(expected: $max, actual: $maxCheckbox->id);
    }
}
