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
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Contact;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

/**
 * Integrity test of RCO Checkout Contact model class.
 */
class ContactTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    private function generateModel(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phone = null
    ): void {
        new Contact(firstName: $firstName, lastName: $lastName, phone: $phone);
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
            $this->fail(message: 'Failed to generate model instance.');
        }
    }

    /**
     * Assert validation rules for firstName property.
     *
     * @throws Exception
     */
    public function testFirstNameValidation(): void
    {
        $firstName1 = Strings::generateRandomString(length: 64);
        $firstName2 = Strings::generateRandomString(length: 65);

        try {
            $this->generateModel(firstName: $firstName1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(message: $firstName1 . ' failed firstName validation.');
        }

        try {
            $this->generateModel(firstName: $firstName2);
            $this->fail(
                message: 'Firstname exceeding 64 characters passed validation.'
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(firstName: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty firstname rejected.');
        }
    }

    /**
     * Assert validation rules for lastName property.
     *
     * @throws Exception
     */
    public function testLastNameValidation(): void
    {
        $lastName1 = Strings::generateRandomString(length: 64);
        $lastName2 = Strings::generateRandomString(length: 65);

        try {
            $this->generateModel(lastName: $lastName1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(message: $lastName1 . ' failed lastName validation.');
        }

        try {
            $this->generateModel(lastName: $lastName2);
            $this->fail(
                message: 'Lastname exceeding 64 characters passed validation.'
            );
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(lastName: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty lastname rejected.');
        }
    }

    /**
     * Assert validation rules for phone property.
     *
     * @throws Exception
     */
    public function testPhoneValidation(): void
    {
        $phone1 = '+123123';
        $phone2 = '+1231234564567890179';
        $phone3 = '556677';

        try {
            $this->generateModel(phone: $phone1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalCharsetException) {
            $this->fail(message: $phone1 . ' failed phone validation.');
        }

        try {
            $this->generateModel(phone: $phone2);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalCharsetException) {
            $this->fail(message: $phone2 . ' failed phone validation.');
        }

        try {
            $this->generateModel(phone: $phone3);
            $this->fail(
                message: 'Phone number without + prefix passed validation.'
            );
        } catch (IllegalCharsetException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(phone: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty phone rejected.');
        }
    }
}
