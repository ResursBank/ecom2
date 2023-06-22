<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Billing address model for RCO+.
 */
class Billing extends Model
{
    public function __construct(
        public readonly string $name,
        public readonly Contact $contact,
        public readonly Address $address
    ) {
    }
}
