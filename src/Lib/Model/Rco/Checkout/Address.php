<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Address model for RCO+ customers.
 */
class Address extends Model
{
    /**
     * @param string $street Street name.
     * @param string $addressLine Extra address line, for c/o or other.
     * @param string $postalCode A valid post code.
     * @param string $city Name of the city.
     * @param string $notes Free text area for notes.
     * @param CountryCode $countryCode ISO 3166-1 Alpha-2 country code.
     */
    public function __construct(
        public readonly string $street,
        public readonly string $addressLine,
        public readonly string $postalCode,
        public readonly string $city,
        public readonly string $notes,
        public readonly CountryCode $countryCode
    ) {
    }
}
