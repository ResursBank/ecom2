<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Address;
use Resursbank\Ecom\Lib\Model\Rco\Contact;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

/**
 * Integrity test of RCO Checkout Recipient model class.
 */
class RecipientTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalValueException
     */
    private function generateModel(): void
    {
        new Recipient(
            name: Strings::generateRandomString(length: 12),
            contact: new Contact(
                firstName: Strings::generateRandomString(length: 12),
                lastName: Strings::generateRandomString(length: 12),
                email: Strings::generateRandomString(length: 12) .
                    '@example.com',
                phone: '+46701234567'
            ),
            address: new Address()
        );
    }

    /**
     * Test generating a valid model instance.
     */
    public function testModel(): void
    {
        try {
            $this->generateModel();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(
                message: 'Failed to generate Recipient model instance.'
            );
        }
    }
}
