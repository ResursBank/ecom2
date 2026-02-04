<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rws\PaymentMethod;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * RWS payment method options.
 */
class MethodOption extends Model
{
    /**
     * @param string $methodId
     * @param bool $selected
     * @param bool $selectable
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly string $methodId,
        public readonly bool $selected,
        public readonly bool $selectable
    ) {
        parent::__construct();
    }
}
