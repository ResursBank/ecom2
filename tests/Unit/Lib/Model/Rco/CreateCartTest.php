<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Rco\CreateCart.
 */
class CreateCartTest extends TestCase
{
    /**
     * Verify that a too long code throws an exception.
     *
     * @throws IllegalValueException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     */
    public function testTooLongCode(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new CreateCart(
            items: new CreateCart\ItemCollection(data: [
                new CreateCart\Item(
                    type: CartItemType::GENERIC,
                    itemId: Strings::generateRandomString(length: 12),
                    description: Strings::generateRandomString(length: 12),
                    quantityUnit: Strings::generateRandomString(length: 2),
                    unitPrice: 1000,
                    quantity: 1
                )
            ]),
            code: Strings::generateRandomString(length: 129)
        );
    }

    /**
     * Verify that valid code values don't throw exceptions.
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testValidCode(): void
    {
        $min = '';
        $max = Strings::generateRandomString(length: 128);

        $minCreateCart = new CreateCart(
            items: new CreateCart\ItemCollection(data: [
                new CreateCart\Item(
                    type: CartItemType::GENERIC,
                    itemId: Strings::generateRandomString(length: 12),
                    description: Strings::generateRandomString(length: 12),
                    quantityUnit: Strings::generateRandomString(length: 2),
                    unitPrice: 1000,
                    quantity: 1
                )
            ]),
            code: $min
        );
        $maxCreateCart = new CreateCart(
            items: new CreateCart\ItemCollection(data: [
                new CreateCart\Item(
                    type: CartItemType::GENERIC,
                    itemId: Strings::generateRandomString(length: 12),
                    description: Strings::generateRandomString(length: 12),
                    quantityUnit: Strings::generateRandomString(length: 2),
                    unitPrice: 1000,
                    quantity: 1
                )
            ]),
            code: $max
        );

        $this->assertEquals(expected: $min, actual: $minCreateCart->code);
        $this->assertEquals(expected: $max, actual: $maxCreateCart->code);
    }

    /**
     * Verify that an item list within the allowed limits doesn't throw exceptions.
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testValidItems(): void
    {
        $minData = [
            new CreateCart\Item(
                type: CartItemType::GENERIC,
                itemId: Strings::generateRandomString(length: 12),
                description: Strings::generateRandomString(length: 32),
                quantity: 1,
                unitPrice: 1000,
                quantityUnit: Strings::generateRandomString(length: 2)
            )
        ];
        $maxData = [];

        for ($i = 0; $i < 256; $i++) {
            $maxData[] = new CreateCart\Item(
                type: CartItemType::GENERIC,
                itemId: Strings::generateRandomString(length: 12),
                description: Strings::generateRandomString(length: 32),
                quantity: 1,
                unitPrice: 1000,
                quantityUnit: Strings::generateRandomString(length: 2)
            );
        }

        $minItems = new CreateCart(
            items: new CreateCart\ItemCollection(data: $minData),
            code: Strings::generateRandomString(length: 32)
        );
        $maxItems = new CreateCart(
            items: new CreateCart\ItemCollection(data: $maxData),
            code: Strings::generateRandomString(length: 32)
        );

        $this->assertCount(
            expectedCount: 1,
            haystack: $minItems->items->toArray()
        );
        $this->assertCount(
            expectedCount: 256,
            haystack: $maxItems->items->toArray()
        );
    }

    /**
     * Verify that an exception is thrown if the item collection is empty.
     *
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testEmptyItems(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new CreateCart(
            items: new CreateCart\ItemCollection(data: []),
            code: Strings::generateRandomString(length: 32)
        );
    }

    /**
     * Verify that an exception is thrown if the item collection is too large.
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testTooManyItems(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $cartData = [];

        for ($i = 0; $i < 257; $i++) {
            $cartData[] = new CreateCart\Item(
                type: CartItemType::GENERIC,
                itemId: Strings::generateRandomString(length: 12),
                description: Strings::generateRandomString(length: 32),
                quantity: 1,
                unitPrice: 1000,
                quantityUnit: Strings::generateRandomString(length: 2)
            );
        }

        new CreateCart(
            items: new CreateCart\ItemCollection(data: $cartData),
            code: Strings::generateRandomString(length: 32)
        );
    }
}
