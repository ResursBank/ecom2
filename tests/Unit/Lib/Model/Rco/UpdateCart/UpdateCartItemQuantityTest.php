<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco\UpdateCart;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\UpdateCart\UpdateCartItemQuantity;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for UpdateCartItemQuantity.
 */
class UpdateCartItemQuantityTest extends TestCase
{
    /**
     * Verify that quantity values within allowed range work as intended.
     *
     * @throws Exception
     */
    public function testValidQuantity(): void
    {
        $min = 1;
        $max = (2 ** 31) - 1;

        $minQuantity = new UpdateCartItemQuantity(
            itemId: Strings::generateRandomString(length: 12),
            quantity: $min
        );
        $maxQuantity = new UpdateCartItemQuantity(
            itemId: Strings::generateRandomString(length: 12),
            quantity: $max
        );

        $this->assertEquals(expected: $min, actual: $minQuantity->quantity);
        $this->assertEquals(expected: $max, actual: $maxQuantity->quantity);
    }

    /**
     * Verify that a quantity value of zero causes an exception to be thrown.
     *
     * @throws Exception
     */
    public function testZeroQuantity(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new UpdateCartItemQuantity(
            itemId: Strings::generateRandomString(length: 12),
            quantity: 0
        );
    }

    /**
     * Verify that a quantity value over 2^31 - 1 causes an exception to be thrown.
     *
     * @throws Exception
     */
    public function testTooLargeQuantity(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new UpdateCartItemQuantity(
            itemId: Strings::generateRandomString(length: 12),
            quantity: 2 ** 31
        );
    }
}
