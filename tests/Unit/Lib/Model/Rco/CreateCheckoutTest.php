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
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;
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
        ?string $orderReference = null,
    ): void {
        new CreateCheckout(
            orderReference: $orderReference ?? Strings::generateRandomString(length: 32),
            cart: new CreateCart(items: new ItemCollection(data: [])),
            merchant: new Merchant(displayName: 'test')
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
            $this->fail(message: 'Failed to generate CreateCheckout model instance.');
        }
    }

    /**
     * Assert validation rules for orderReference property.
     *
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testOrderReferenceValidation(): void
    {
        try {
            $this->generateCheckoutModel(orderReference: '');
            $this->addToAssertionCount(count: 1);
        } catch (EmptyValueException) {
            $this->fail(message: 'Empty orderReference value rejected.');
        }

        try {
            $this->generateCheckoutModel(
                orderReference: Strings::generateRandomString(length: 32)
            );

            $this->addToAssertionCount(count: 1);
        } catch (IllegalCharsetException) {
            $this->fail(message: '32 character orderReference value rejected.');
        }

        try {
            $this->generateCheckoutModel(
                orderReference: Strings::generateRandomString(length: 33)
            );

            $this->fail(message: '33 character orderReference value accepted.');
        } catch (IllegalCharsetException) {
            $this->addToAssertionCount(count: 1);
        }
    }
}
