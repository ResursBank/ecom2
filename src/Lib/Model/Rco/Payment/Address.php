<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

class Address extends Model
{
    public function __construct(
        private readonly string $street,
        private readonly string $addressLine,
        private readonly string $postalCode,
        private readonly string $city,
        private readonly string $notes,
        private readonly CountryCode $countryCode
    ) {
    }
}
