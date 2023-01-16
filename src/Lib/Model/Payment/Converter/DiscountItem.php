<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Converter;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Object containing amount of discount applied with specific VAT rate.
 */
class DiscountItem extends Model
{
    /**
     * Setup properties.
     */
    public function __construct(
        public readonly float $rate,
        public float $amount = 0.0
    ) { }
}
