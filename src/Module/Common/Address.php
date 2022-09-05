<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Common;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Address information block about a payment.
 */
class Address extends Model
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $addressRow1,
        public readonly string $postalArea,
        public readonly string $postalCode,
        public readonly ?string $countryCode = '',
        public readonly ?string $firstName = '',
        public readonly ?string $lastName = '',
        public readonly ?string $addressRow2 = null,
    ) {
    }
}
