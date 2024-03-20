<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco\Shipping;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Option;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Rco\Shipping\Option.
 */
class OptionTest extends TestCase
{
    /**
     * Verify that the optionId parameter behaves as intended.
     *
     * @throws Exception
     */
    public function testOptionId(): void
    {
        $min = 1;
        $max = 128;

        // Too short
        try {
            new Option(
                optionId: '',
                name: Strings::generateRandomString(length: 12)
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        // Too long
        try {
            new Option(
                optionId: Strings::generateRandomString(length: $max + 1),
                name: Strings::generateRandomString(length: 12)
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        // On limit
        $minString = Strings::generateRandomString(length: $min);
        $maxString = Strings::generateRandomString(length: $max);
        $minOption = new Option(
            optionId: $minString,
            name: Strings::generateRandomString(length: 12)
        );
        $maxOption = new Option(
            optionId: $maxString,
            name: Strings::generateRandomString(length: 12)
        );

        $this->assertEquals(expected: $minString, actual: $minOption->optionId);
        $this->assertEquals(expected: $maxString, actual: $maxOption->optionId);
    }

    /**
     * Verify that the name parameter behaves as intended.
     *
     * @throws Exception
     */
    public function testName(): void
    {
        $min = 1;
        $max = 128;

        // Too short
        try {
            new Option(
                optionId: Strings::generateRandomString(length: 12),
                name: ''
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        // Too long
        try {
            new Option(
                optionId: Strings::generateRandomString(length: 12),
                name: Strings::generateRandomString(length: $max + 1)
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        // On limit
        $minString = Strings::generateRandomString(length: $min);
        $maxString = Strings::generateRandomString(length: $max);
        $minName = new Option(
            optionId: Strings::generateRandomString(length: 12),
            name: $minString
        );
        $maxName = new Option(
            optionId: Strings::generateRandomString(length: 12),
            name: $maxString
        );

        $this->assertEquals(expected: $minString, actual: $minName->name);
        $this->assertEquals(expected: $maxString, actual: $maxName->name);
    }

    /**
     * Verify that the description parameter behaves as intended.
     *
     * @throws Exception
     */
    public function testDescription(): void
    {
        $max = 256;

        try {
            new Option(
                optionId: Strings::generateRandomString(length: 12),
                name: Strings::generateRandomString(length: 12),
                description: Strings::generateRandomString(length: $max + 1)
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        $validDescription = Strings::generateRandomString(length: $max);
        $validOption = new Option(
            optionId: Strings::generateRandomString(length: 12),
            name: Strings::generateRandomString(length: 12),
            description: $validDescription
        );

        $this->assertEquals(
            expected: $validDescription,
            actual: $validOption->description
        );
    }

    /**
     * Verify that the openingHours parameter behaves as intended.
     *
     * @throws Exception
     */
    public function testOpeningHours(): void
    {
        // Too long
        $openingHours = [];

        for ($i = 0; $i < 37; $i++) {
            $openingHours[] = Strings::generateRandomString(length: 12);
        }

        try {
            new Option(
                optionId: Strings::generateRandomString(length: 12),
                name: Strings::generateRandomString(length: 12),
                openingHours: $openingHours
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        // Non-string
        $openingHours = [
            Strings::generateRandomString(length: 12),
            42
        ];

        try {
            new Option(
                optionId: Strings::generateRandomString(length: 12),
                name: Strings::generateRandomString(length: 12),
                openingHours: $openingHours
            );
        } catch (IllegalTypeException) {
            $this->addToAssertionCount(count: 1);
        }

        // Valid
        $openingHours = [
            Strings::generateRandomString(length: 12),
            Strings::generateRandomString(length: 12)
        ];
        $option = new Option(
            optionId: Strings::generateRandomString(length: 12),
            name: Strings::generateRandomString(length: 12),
            openingHours: $openingHours
        );

        $this->assertEqualsCanonicalizing(
            expected: $openingHours,
            actual: $option->openingHours
        );
    }
}
