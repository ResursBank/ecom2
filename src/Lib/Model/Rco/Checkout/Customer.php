<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer model for RCO+.
 */
class Customer extends Model
{
    /**
     * @param CustomerType|null $type Customer type enum.
     * @param string|null $governmentId Government id supplied by the customer.
     * @param Billing|null $billing Billing address object.
     * @param Delivery|null $delivery Delivery address object.
     */
    public function __construct(
        public readonly ?CustomerType $type = null,
        public readonly ?string $governmentId = null,
        public readonly ?Billing $billing = null,
        public readonly ?Delivery $delivery = null
    ) {
    }
}
