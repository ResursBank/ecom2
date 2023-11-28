<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;

/**
 * Implementation of TransactionLineDto
 */
class Transaction extends Model
{
    public function __construct(
        public readonly CartItemType $type,
        #[StringLength(min: 0, max: 280)] public readonly string $description,
        public readonly ?string $itemId = null,
        #[StringLength(
            min: 1,
            max: 32
        )] public readonly ?string $quantityUnit = null,
        #[IntValue(
            min: 1,
            max: (2 ** 31) - 1
        )] public readonly ?int $quantity = null,
        #[IntValue(
            min: -(2 ** 31) + 1,
            max: (2 ** 31) - 1
        )] public readonly ?int $unitPrice = null,
        #[IntValue(min: 0, max: 100)] public readonly ?int $taxRate = null
    ) {
        parent::__construct();
    }
}
