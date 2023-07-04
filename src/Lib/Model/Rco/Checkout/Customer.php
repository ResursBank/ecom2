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
     * @param CustomerType $type Customer type enum.
     * @param string $governmentId Government id supplied by the customer.
     * @param Billing $billing Billing address object.
     * @param Delivery|null $delivery Delivery address object.
     */
    public function __construct(
        public readonly CustomerType $type,
        public readonly string $governmentId,
        public readonly Billing $billing,
        public readonly ?Delivery $delivery = null
    ) {
    }
}
