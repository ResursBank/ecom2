<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Cart;

use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
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
        public readonly CartItemType $type,
        public readonly string $itemId,
        public readonly string $description,
        public readonly string $quantityUnit,
        public readonly int $quantity,
        public readonly int $unitPrice,
        public readonly int $totalPrice,
        public readonly int $taxRate,
        public readonly int $totalTax,
        public readonly int $totalDiscount,
        public readonly string $url,
        public readonly string $imageUrl,
        // Ignoring complaint about $tags being unused
        // phpcs:ignore
        #[ArrayOfStrings] readonly array $tags
    ) {
        parent::__construct();
    }
}
