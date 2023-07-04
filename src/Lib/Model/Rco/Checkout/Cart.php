<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Cart model for RCO+.
 */
class Cart extends Model
{
    public function __construct(
        public readonly string $code,
        public readonly ItemCollection $items
    ) {
    }
}
