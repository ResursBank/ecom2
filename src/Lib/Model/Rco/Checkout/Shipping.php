<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Selection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\ShippingMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Tracking;

/**
 * Shipping property of Checkout model
 */
class Shipping extends Model
{
    public function __construct(
        public readonly ?Tracking $tracking = null,
        public readonly ?Selection $selection = null,
        public readonly ?ShippingMethodCollection $methods = null
    ) {
    }
}
