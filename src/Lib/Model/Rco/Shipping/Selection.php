<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\ShippingSelection;

/**
 * Implementation of ShippingSelectionDto object.
 */
class Selection extends Model
{
    public function __construct(
        public readonly string $methodId,
        public readonly string $optionId,
        public readonly ShippingSelection $type
    ) {
    }
}
