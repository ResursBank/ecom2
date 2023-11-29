<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CreateContactDto object.
 */
class CreateContact extends Model
{
    /**
     * @param string|null $firstName The firstName of the contact.
     * @param string|null $lastName The lastName of the contact.
     * @param string|null $email The email address to the contact person.
     * @param string|null $phone The phone number to the contact person.
     */
    public function __construct(
        #[StringLength(
            min: 0,
            max: 64
        )] public readonly ?string $firstName = null,
        #[StringLength(
            min: 0,
            max: 64
        )] public readonly ?string $lastName = null,
        public readonly ?string $email = null,
        #[StringMatchesRegex(
            '/^$|^\+\d{6,19}$/'
        )] public readonly ?string $phone = null
    ) {
        parent::__construct();
    }
}
