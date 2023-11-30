<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateAddress;
use Resursbank\Ecom\Lib\Model\Rco\CreateContact;

/**
 * Implementation of CreateRecipientDto object.
 */
class CreateRecipient extends Model
{
    /**
     * @param string|null $name The full name of the customer.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(min: 0, max: 128)] public readonly ?string $name = null,
        public readonly ?CreateContact $contact = null,
        public readonly ?CreateAddress $address = null
    ) {
        parent::__construct();
    }
}
