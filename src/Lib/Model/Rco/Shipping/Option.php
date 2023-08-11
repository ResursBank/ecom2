<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Address;

/**
 * Implementation of ShippingOptionDto object.
 */
class Option extends Model
{
    public function __construct(
        public readonly string $optionId,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?Address $address = null,
        public readonly ?array $openingHours = null
    ) {
    }
}
