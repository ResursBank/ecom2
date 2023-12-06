<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of ContactDto object.
 */
class Contact extends Model
{
    /**
     * @param string $firstName The firstName of the contact.
     * @param string $lastName The lastName of the contact.
     * @param string $email The email address to the contact person.
     * @param string $phone The phone number to the contact person.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(min: 0, max: 64)] public readonly string $firstName,
        #[StringLength(min: 0, max: 64)] public readonly string $lastName,
        public readonly string $email,
        #[StringLength(min: 0, max: 20)]
        #[StringMatchesRegex(pattern: '/^$|^\+\d{6,19}$/')]
        public readonly string $phone
    ) {
        parent::__construct();
    }
}
