<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Cart;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;

/**
 * Implementation of CartItemDto object.
 */
class Item extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $description,
        public readonly string $imageUrl,
        #[StringLength(min: 1, max: 255)] public readonly string $itemId,
        #[StringLength(min: 1, max: 255)] public readonly string $itemIdDisplay,
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
