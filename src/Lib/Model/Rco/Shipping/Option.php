<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Address;

/**
 * Implementation of ShippingOptionDto object.
 */
class Option extends Model
{
    public function __construct(
        #[StringLength(
            min: 1,
            max: 128
        )] #[StringNotEmpty] public readonly string $optionId,
        #[StringLength(
            min: 1,
            max: 128
        )] #[StringNotEmpty] public readonly string $name,
        #[StringLength(max: 256)] public readonly ?string $description = null,
        public readonly ?Address $address = null,
        #[ArraySize(
            max: 36
        )] #[ArrayOfStrings] public readonly ?array $openingHours = null
    ) {
        parent::__construct();
    }
}
