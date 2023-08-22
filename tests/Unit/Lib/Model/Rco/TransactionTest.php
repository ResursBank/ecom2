<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Model\Rco\Transaction;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Rco\Transaction
 */
class TransactionTest extends TestCase
{
    /**
     * Assert that an error is thrown when description exceeds allowed length.
     *
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testInvalidDescription(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 281)
        );
    }

    /**
     * Assert that an error is thrown when quantity unit exceeds allowed length.
     *
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testInvalidQuantityUnit(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            quantityUnit: Strings::generateRandomString(length: 33)
        );
    }

    /**
     * Assert that an error is thrown when quantity is negative.
     *
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testNegativeQuantity(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            quantity: -1
        );
    }

    /**
     * Assert that an error is thrown when quantity is zero.
     *
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testZeroQuantity(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            quantity: 0
        );
    }

    /**
     * Assert that an error is thrown when quantity is too large.
     *
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testTooLargeQuantity(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            quantity: 2 ** 31
        );
    }

    /**
     * Assert that valid values are correctly set on quantity.
     *
     * @throws Exception
     */
    public function testValidQuantities(): void
    {
        $lowerLimit = new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            quantity: 1
        );
        $upperLimit = new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            quantity: (2 ** 31) - 1
        );

        $this->assertEquals(expected: 1, actual: $lowerLimit->quantity);
        $this->assertEquals(
            expected: (2 ** 31) - 1,
            actual: $upperLimit->quantity
        );
    }

    /**
     * Assert that exception is thrown if unit price is set too low.
     *
     * @throws Exception
     */
    public function testTooLowUnitPrice(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            unitPrice: -1
        );
    }

    /**
     * Assert that exception is thrown if unit price is set too high.
     *
     * @throws Exception
     */
    public function testTooHighUnitPrice(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            unitPrice: 2 ** 31
        );
    }

    /**
     * Verify that valid unit prices are accepted.
     *
     * @throws Exception
     */
    public function testValidUnitPrice(): void
    {
        $lowerLimit = new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            unitPrice: 0
        );
        $upperLimit = new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            unitPrice: (2 ** 31) - 1
        );

        $this->assertEquals(expected: 0, actual: $lowerLimit->unitPrice);
        $this->assertEquals(
            expected: (2 ** 31) - 1,
            actual: $upperLimit->unitPrice
        );
    }

    /**
     * Assert that exception is thrown if tax rate is set too low.
     *
     * @throws Exception
     */
    public function testTooLowTaxRate(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            taxRate: -1
        );
    }

    /**
     * Assert that exception is thrown if tax rate is set too high.
     *
     * @throws Exception
     */
    public function testTooHighTaxRate(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        new Transaction(
            type: CartItemType::GENERIC,
            description: Strings::generateRandomString(length: 32),
            taxRate: 101
        );
    }
}
