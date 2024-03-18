<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;

/**
 * Implementation of TransactionLineDto
 */
class TransactionLine extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly CartItemType $type,
        #[StringLength(min: 0, max: 280)] public readonly string $description,
        #[StringLength(min: 1, max: 255)] public readonly string $itemId,
        #[StringLength(min: 1, max: 255)] public readonly string $itemIdDisplay,
        #[StringLength(min: 1, max: 32)] public readonly string $quantityUnit,
        #[IntValue(min: 1, max: (2 ** 31) - 1)] public readonly int $quantity,
        #[IntValue(min: 0, max: (2 ** 31) - 1)] public readonly int $unitPrice,
        #[IntValue(min: 0, max: 100)] public readonly int $taxRate
    ) {
        parent::__construct();
    }
}
