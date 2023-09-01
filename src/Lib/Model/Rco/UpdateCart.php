<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\UpdateCart\UpdateCartItemQuantityCollection;

/**
 * Implementation of UpdateCartDto.
 */
class UpdateCart extends Model
{
    public function __construct(
        public readonly ?UpdateCartItemQuantityCollection $items,
        public readonly ?string $cartCode
    ) {
        parent::__construct();
    }
}
