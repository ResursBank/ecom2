<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of ContactDot object.
 */
class Contact extends Model
{
    /**
     * @param string|null $firstName The firstName of the contact.
     * @param string|null $lastName The lastName of the contact.
     * @param string|null $email The email address to the contact person.
     * @param string|null $phone The phone number to the contact person.
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateFirstName();
        $this->validateLastName();
        $this->validatePhone();
    }

    /**
     * @throws IllegalValueException
     */
    public function validateFirstName(): void
    {
        if ($this->firstName === null) {
            return;
        }

        $this->stringValidation->length(
            value: $this->firstName,
            min: 0,
            max: 64
        );
    }

    /**
     * @throws IllegalValueException
     */
    public function validateLastName(): void
    {
        if ($this->lastName === null) {
            return;
        }

        $this->stringValidation->length(
            value: $this->lastName,
            min: 0,
            max: 64
        );
    }

    /**
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function validatePhone(): void
    {
        if ($this->phone === null) {
            return;
        }

        $this->stringValidation->length(value: $this->phone, min: 0, max: 20);

        $this->stringValidation->matchRegex(
            value: $this->phone,
            pattern: '/^$|^\+\d{6,19}$/'
        );
    }
}
