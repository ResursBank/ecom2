<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Shipping methods model, for RCO+.
 *
 * @see https://web-integration-rco-plus.integration.resurs.com/docs/#_set_shipping_methods
 */
class ShippingMethodCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: ShippingMethod::class);
    }
}
