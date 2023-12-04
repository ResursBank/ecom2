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
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\UpdateCart\UpdateCartItemQuantityCollection;

/**
 * Implementation of UpdateCartDto.
 */
class UpdateCart extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly ?UpdateCartItemQuantityCollection $items,
        public readonly ?string $cartCode
    ) {
        parent::__construct();
    }
}
