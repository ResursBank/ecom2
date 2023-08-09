<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;

/**
 * Implementation of CreateCartDto object (used in both POST and PUT, so it's
 * both to create and update the cart at Resurs Bank).
 */
class CreateCart extends Model
{
    public function __construct(
        public readonly ItemCollection $items,
        public readonly ?string $code = null
    ) {
    }
}
