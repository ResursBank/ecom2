<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethodElements\Session;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Embed object in Payment Elements session.
 */
class Embed extends Model
{
    /**
     * @param string $src Script src for Payment Elements initialization.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly string $src
    ) {
        parent::__construct();
    }
}
