<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\MethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Selection;

/**
 * Implementation of ShippingDto object.
 */
class Shipping extends Model
{
    public function __construct(
        public readonly Tracking $tracking,
        public readonly Selection $selection,
        public readonly MethodCollection $methods
    ) {
    }
}
