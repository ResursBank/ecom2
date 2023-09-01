<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;

/**
 * Implementation of CreateCartDto object (used in both POST and PUT, so it's
 * both to create and update the cart at Resurs Bank).
 */
class CreateCart extends Model
{
    public function __construct(
        #[CollectionSize(
            min: 1,
            max: 256
        )] public readonly ItemCollection $items,
        #[StringLength(min: 0, max: 128)] public readonly ?string $code = null
    ) {
        parent::__construct();
    }
}
