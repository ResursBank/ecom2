<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\UpdateCart;

use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of UpdateCartItemQuantityDto
 */
class UpdateCartItemQuantity extends Model
{
    public function __construct(
        public readonly string $itemId,
        #[IntValue(min: 1, max: (2 ** 31) - 1)] public readonly int $quantity
    ) {
        parent::__construct();
    }
}
