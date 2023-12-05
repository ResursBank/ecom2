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

/**
 * Implementation of UpdateShippingMethodsDto object.
 */
class UpdateShippingMethods extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly CreateShippingMethodCollection $methods
    ) {
        parent::__construct();
    }
}
