<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of ShippingPriceDto object.
 */
class Price extends Model
{
    /**
     * @param string $display The display price, this is shown in the checkout.
     * @param int $calculate Price inc tax in minor units create a shipping-cart line if calculateShipping is true.
     * @param int $calculateTax The tax rate used for the calculation as a whole number: 25 for 25%.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(
            min: 0,
            max: 128
        )] public readonly string $display,
        public readonly int $calculate,
        public readonly int $calculateTax
    ) {
        parent::__construct();
    }
}
