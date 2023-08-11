<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Cart;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;

use function is_string;

/**
 * Implementation of CartItemDto object.
 */
class Item extends Model
{
    /**
     * @throws IllegalTypeException
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
        public readonly array $tags,
        private readonly ArrayValidation $arrayValidation = new ArrayValidation()
    ) {
        $this->validateTags();
    }

    /**
     * @throws IllegalTypeException
     */
    private function validateTags(): void
    {
        $this->arrayValidation->isOfType(
            data: $this->tags,
            type: 'string',
            compareFn: static fn (mixed $value) => is_string(value: $value)
        );
    }
}
