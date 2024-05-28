<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCart;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;

/**
 * Implementation of CrateCartItemDto object.
 */
class Item extends Model
{
    /**
     * @param array|null $tags
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly CartItemType $type,
        #[StringLength(min: 1, max: 255)] public readonly string $itemId,
        #[StringLength(min: 0, max: 280)] public readonly string $description,
        #[StringLength(min: 1, max: 32)] public readonly string $quantityUnit,
        public readonly int $unitPrice,
        #[IntValue(min: 0, max: (2 ** 31) - 1)] public readonly int $quantity,
        #[IntValue(min: 0, max: 100)] public readonly int $taxRate,
        #[StringLength(min: 1, max: 255)]
        public readonly ?string $itemIdDisplay = null,
        #[IntValue(
            min: -(2 ** 31) + 1,
            max: 0
        )] public readonly ?int $totalDiscount = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/'
        )]
        public readonly ?string $url = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/'
        )]
        public readonly ?string $imageUrl = null,
        #[ArrayOfStrings] #[ArraySize(
            max: 10
        )] public readonly ?array $tags = null,
        public readonly ?bool $mutable = null,
        #[IntValue(min: 0, max: 1000)] public readonly ?int $maxQuantity = null
    ) {
        parent::__construct();
    }
}
