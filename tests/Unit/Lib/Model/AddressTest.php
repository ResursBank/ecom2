<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Address.
 */
class AddressTest extends TestCase
{
    /**
     * Generate an Address object with configurable postalCode.
     */
    private function generateAddress(string $postalCode): Address
    {
        return new Address(
            addressRow1: Strings::generateRandomString(length: 24),
            postalArea: Strings::generateRandomString(length: 24),
            postalCode: $postalCode
        );
    }

    /**
     * Assert that an exception is thrown if postalCode is empty.
     */
    public function testEmptyPostalCode(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->generateAddress(postalCode: '');
    }

    /**
     * Assert that an exception is thrown if postalCode is longer than 5 chars.
     */
    public function testTooLongPostalCode(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->generateAddress(
            postalCode: Strings::generateRandomString(length: 6)
        );
    }

    /**
     * Assert that an exception is thrown for non-numerical postalCode.
     *
     * @throws Exception
     */
    public function testInvalidCharactersInPostalCode(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->generateAddress(
            postalCode: Strings::generateRandomString(
                length: 5,
                characters: 'abcdefghjijklmnopqrstuv/&%€#DJKW'
            )
        );
    }
}
