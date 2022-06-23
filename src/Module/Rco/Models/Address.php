<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models;

class Address
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $addressRow1 = null,
        public ?string $addressRow2 = null,
        public ?string $postalArea = null,
        public ?string $postalCode = null,
        public ?string $countryCode = null
    ) {
    }
}
