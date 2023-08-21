<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Cart\ItemCollection;

/**
 * Implementation of CartDto object.
 */
class Cart extends Model
{
    public function __construct(
        public readonly ItemCollection $items,
        #[StringLength(min: 10, max: 1000)] public readonly string $code
    ) {
    }
}
