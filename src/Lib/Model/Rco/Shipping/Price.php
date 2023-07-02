<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Price for shippingMethod, used in RCO+.
 */
class Price extends Model
{
    /**
     * @param string $display The display price, this is shown in the checkout.
     * @param int $calculate Price inc tax in minor units create a shipping-cart line if calculateShipping is true.
     * @param int $calculateTax The tax rate used for the calculation as a whole number: 25 for 25%.
     */
    public function __construct(
        public readonly string $display,
        public readonly int $calculate,
        public readonly int $calculateTax
    ) {
    }
}
