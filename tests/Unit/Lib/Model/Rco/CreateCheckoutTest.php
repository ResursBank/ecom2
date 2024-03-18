<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateMerchant;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

/**
 * Integrity test of RCO Checkout model class utilised in POST requests.
 */
class CreateCheckoutTest extends TestCase
{
    /**
     * Get mocked Checkout model instance.
     *
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws Exception
     */
    private function generateCheckoutModel(
        ?string $orderReference = null
    ): void {
        new CreateCheckout(
            orderReference: $orderReference ?? Strings::generateRandomString(
                length: 32
            ),
            cart: new CreateCart(items: new ItemCollection(data: [
                new CreateCart\Item(
                    type: CartItemType::PRODUCT,
                    itemId: 'item-001',
                    description: 'An item',
                    quantityUnit: 'st',
                    unitPrice: 1500,
                    quantity: 1,
                    taxRate: 25
                )
            ])),
            merchant: new CreateMerchant(
                displayName: 'test',
                termsUrl: 'https://example.com'
            )
        );
    }

    /**
     * Test generating a valid Checkout model instance.
     */
    public function testCheckoutModel(): void
    {
        try {
            $this->generateCheckoutModel();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(
                message: 'Failed to generate CreateCheckout model instance.'
            );
        }
    }
}
