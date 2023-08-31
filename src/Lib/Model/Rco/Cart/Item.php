<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Cart;

use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;

/**
 * Implementation of CartItemDto object.
 */
class Item extends Model
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $description,
        public readonly string $imageUrl,
        public readonly string $itemId,
        #[IntValue(max: (2 ** 31) - 1)] public readonly int $maxQuantity,
        public readonly bool $mutable,
        #[IntValue(max: (2 ** 31) - 1)] public readonly int $quantity,
        public readonly string $quantityUnit,
        #[ArrayOfStrings] public readonly array $tags,
        #[IntValue(max: (2 ** 31) - 1)] public readonly int $taxRate,
        #[IntValue(max: (2 ** 31) - 1)] public readonly int $totalDiscount,
        #[IntValue(max: (2 ** 31) - 1)] public readonly int $totalPrice,
        #[IntValue(max: (2 ** 31) - 1)] public readonly int $totalTax,
        public readonly CartItemType $type,
        #[IntValue(max: (2 ** 31) - 1)] public readonly int $unitPrice,
        public readonly string $url
    ) {
        parent::__construct();
    }
}
