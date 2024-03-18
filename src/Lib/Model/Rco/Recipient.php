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
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of RecipientDto object.
 */
class Recipient extends Model
{
    /**
     * @param string $name The full name of the customer.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(min: 0, max: 128)] public readonly string $name,
        public readonly Contact $contact,
        public readonly Address $address
    ) {
        parent::__construct();
    }
}
