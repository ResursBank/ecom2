<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\UpdateCart;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of UpdateCartItemQuantityDto
 */
class UpdateCartItemQuantity extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly string $itemId,
        #[IntValue(min: 1, max: (2 ** 31) - 1)] public readonly int $quantity
    ) {
        parent::__construct();
    }
}
