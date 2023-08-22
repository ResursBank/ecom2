<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco\Shipping;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Rco\Shipping\Price
 */
class PriceTest extends TestCase
{
    /**
     * Assert that an error is thrown when display exceeds allowed length.
     *
     * @throws Exception
     */
    public function testInvalidDisplay(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Price(
            display: Strings::generateRandomString(length: 129),
            calculate: 10,
            calculateTax: 10
        );
    }

    /**
     * Assert that valid display lengths work
     *
     * @throws Exception
     */
    public function testValidDisplay(): void
    {
        $short = Strings::generateRandomString(length: 1);
        $long = Strings::generateRandomString(length: 128);

        $shortPrice = new Price(
            display: $short,
            calculate: 10,
            calculateTax: 10
        );
        $longPrice = new Price(display: $long, calculate: 10, calculateTax: 10);

        $this->assertEquals(expected: $short, actual: $shortPrice->display);
        $this->assertEquals(expected: $long, actual: $longPrice->display);
    }
}
