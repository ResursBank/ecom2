<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer contact information for RCO+.
 */
class Contact extends Model
{
    /**
     * @param string $firstName The firstName of the contact.
     * @param string $lastName The lastName of the contact.
     * @param string $email The email address to the contact person.
     * @param string $phone The phone number to the contact person.
     */
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone
    ) {
    }
}
