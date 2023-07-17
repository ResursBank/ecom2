<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Billing address model for RCO+.
 */
class Billing extends Model
{
    /**
     * @param string|null $name The full name of the customer.
     * @param Contact|null $contact Contact class.
     * @param Address|null $address Address class.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?Contact $contact = null,
        public readonly ?Address $address = null
    ) {
    }
}
