<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\UpdateCart;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * UpdateCartItemQuantity collection.
 */
class UpdateCartItemQuantityCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: UpdateCartItemQuantity::class);
    }
}
