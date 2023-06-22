<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer model for RCO+.
 */
class Customer extends Model
{
    /**
     * @param Delivery $delivery Delivery address object.
     * @param CustomerType $type Customer type enum.
     * @param string $governmentId Government id supplied by the customer.
     * @param Billing $billing Billing address object.
     */
    public function __construct(
        public readonly Delivery $delivery,
        public readonly CustomerType $type,
        public readonly string $governmentId,
        public readonly Billing $billing
    ) {
    }
}
