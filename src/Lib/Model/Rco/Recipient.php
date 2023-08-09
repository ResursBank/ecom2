<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Address;
use Resursbank\Ecom\Lib\Model\Rco\Contact;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of RecipientDto object.
 */
class Recipient extends Model
{
    /**
     * @param string|null $name The full name of the customer.
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?Contact $contact = null,
        public readonly ?Address $address = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateName();
    }

    /**
     * @throws IllegalValueException
     */
    public function validateName(): void
    {
        if ($this->name !== null) {
            $this->stringValidation->length(value: $this->name, min: 0, max: 128);
        }
    }
}
