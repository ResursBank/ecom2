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
use Resursbank\Ecom\Lib\Model\Rco\Address;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

/**
 * Integrity test of RCO Checkout Address model class.
 */
class AddressTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalValueException
     */
    private function generateModel(
        ?string $street = null,
        ?string $addressLine = null,
        ?string $postalCode = null,
        ?string $city = null,
        ?string $notes = null
    ): void {
        new Address(
            street: $street,
            addressLine: $addressLine,
            postalCode: $postalCode,
            city: $city,
            notes: $notes,
            countryCode: CountryCode::SE
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
            $this->fail(message: 'Failed to generate model instance.');
        }
    }

    /**
     * Assert validation rules for street property.
     *
     * @throws Exception
     */
    public function testStreetValidation(): void
    {
        $street1 = Strings::generateRandomString(length: 80);
        $street2 = Strings::generateRandomString(length: 81);

        try {
            $this->generateModel(street: $street1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(message: $street1 .' failed street validation.');
        }

        try {
            $this->generateModel(street: $street2);
            $this->fail(message: 'Street exceeding 64 characters passed validation.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(street: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty street rejected.');
        }
    }

    /**
     * Assert validation rules for addressLine property.
     *
     * @throws Exception
     */
    public function testAddressLineValidation(): void
    {
        $addressLine1 = Strings::generateRandomString(length: 80);
        $addressLine2 = Strings::generateRandomString(length: 81);

        try {
            $this->generateModel(addressLine: $addressLine1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(message: $addressLine1 .' failed addressLine validation.');
        }

        try {
            $this->generateModel(addressLine: $addressLine2);
            $this->fail(message: 'AddressLine exceeding 64 characters passed validation.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(addressLine: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty addressLine rejected.');
        }
    }

    /**
     * Assert validation rules for postalCode property.
     *
     * @throws Exception
     */
    public function testPostalCodeValidation(): void
    {
        $postalCode1 = Strings::generateRandomString(length: 24);
        $postalCode2 = Strings::generateRandomString(length: 25);

        try {
            $this->generateModel(postalCode: $postalCode1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(message: $postalCode1 .' failed postalCode validation.');
        }

        try {
            $this->generateModel(postalCode: $postalCode2);
            $this->fail(message: 'PostalCode exceeding 64 characters passed validation.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(postalCode: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty postalCode rejected.');
        }
    }

    /**
     * Assert validation rules for city property.
     *
     * @throws Exception
     */
    public function testCityValidation(): void
    {
        $city1 = Strings::generateRandomString(length: 80);
        $city2 = Strings::generateRandomString(length: 81);

        try {
            $this->generateModel(city: $city1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(message: $city1 .' failed city validation.');
        }

        try {
            $this->generateModel(city: $city2);
            $this->fail(message: 'City exceeding 64 characters passed validation.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(city: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty city rejected.');
        }
    }

    /**
     * Assert validation rules for notes property.
     *
     * @throws Exception
     */
    public function testNotesValidation(): void
    {
        $notes1 = Strings::generateRandomString(length: 280);
        $notes2 = Strings::generateRandomString(length: 281);

        try {
            $this->generateModel(notes: $notes1);
            $this->addToAssertionCount(count: 1);
        } catch (IllegalValueException) {
            $this->fail(message: $notes1 .' failed notes validation.');
        }

        try {
            $this->generateModel(notes: $notes2);
            $this->fail(message: 'Notes exceeding 280 characters passed validation.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateModel(notes: '');
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Empty notes rejected.');
        }
    }
}
