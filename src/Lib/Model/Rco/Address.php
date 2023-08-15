<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of AddressDto object.
 */
class Address extends Model
{
    /**
     * @param string|null $street Street name.
     * @param string|null $addressLine Extra address line, for c/o or other.
     * @param string|null $postalCode A valid post code.
     * @param string|null $city Name of the city.
     * @param string|null $notes Free text area for notes.
     * @param CountryCode|null $countryCode ISO 3166-1 Alpha-2 country code.
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly ?string $street = null,
        public readonly ?string $addressLine = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $city = null,
        public readonly ?string $notes = null,
        public readonly ?CountryCode $countryCode = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateStreet();
        $this->validateAddressLine();
        $this->validatePostalCode();
        $this->validateCity();
        $this->validateNotes();
    }

    /**
     * @throws IllegalValueException
     */
    public function validateStreet(): void
    {
        if ($this->street === null) {
            return;
        }

        $this->stringValidation->length(value: $this->street, min: 0, max: 80);
    }

    /**
     * @throws IllegalValueException
     */
    public function validateAddressLine(): void
    {
        if ($this->addressLine === null) {
            return;
        }

        $this->stringValidation->length(
            value: $this->addressLine,
            min: 0,
            max: 80
        );
    }

    /**
     * @throws IllegalValueException
     */
    public function validatePostalCode(): void
    {
        if ($this->postalCode === null) {
            return;
        }

        $this->stringValidation->length(
            value: $this->postalCode,
            min: 0,
            max: 24
        );
    }

    /**
     * @throws IllegalValueException
     */
    public function validateCity(): void
    {
        if ($this->city === null) {
            return;
        }

        $this->stringValidation->length(value: $this->city, min: 0, max: 80);
    }

    /**
     * @throws IllegalValueException
     */
    public function validateNotes(): void
    {
        if ($this->notes === null) {
            return;
        }

        $this->stringValidation->length(value: $this->notes, min: 0, max: 280);
    }
}
