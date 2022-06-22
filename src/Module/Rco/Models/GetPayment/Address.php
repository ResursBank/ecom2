<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\GetPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines a GetPayment address object
 */
class Address extends Model
{
    /**
     * @param string $fullName
     * @param string $firstName
     * @param string $lastName
     * @param string $addressRow1
     * @param string|null $addressRow2
     * @param string $postalArea
     * @param string $postalCode
     * @param string $country
     */
    public function __construct(
        public string $fullName,
        public string $firstName,
        public string $lastName,
        public string $addressRow1,
        public ?string $addressRow2,
        public string $postalArea,
        public string $postalCode,
        public string $country
    ) {
    }
}
