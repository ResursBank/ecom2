<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\Cart;
use Resursbank\Ecom\Lib\Model\Rco\Cart\Item;
use Resursbank\Ecom\Lib\Model\Rco\Cart\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Rco\Cart.
 */
class CartTest extends TestCase
{
    /**
     * Generate an item collection.
     *
     * @throws IllegalTypeException
     */
    private function getItems(): ItemCollection
    {
        return new ItemCollection(data: [
            new Item(
                type: CartItemType::GENERIC,
                itemId: Strings::generateRandomString(length: 12),
                itemIdDisplay: Strings::generateRandomString(length: 12),
                description: Strings::generateRandomString(length: 12),
                quantityUnit: Strings::generateRandomString(length: 2),
                quantity: 1,
                unitPrice: 4200,
                totalPrice: 4200,
                taxRate: 0,
                totalTax: 0,
                totalDiscount: 0,
                url: 'https://example.com',
                imageUrl: 'https://example.com',
                tags: [],
                mutable: false,
                maxQuantity: (2 ** 31 ) - 1
            )
        ]);
    }

    /**
     * Verify that an object is created when valid data is fed to constructor.
     *
     * @throws IllegalTypeException
     */
    public function testCreate(): void
    {
        $cart = new Cart(
            items: $this->getItems(),
            code: Strings::generateRandomString(length: 12)
        );
        $this->assertInstanceOf(expected: Cart::class, actual: $cart);
    }
}
