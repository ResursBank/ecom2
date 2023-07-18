<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\PaymentMethods;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines the currently selected payment method.
 */
class Selection extends Model
{
    public function __construct(
        public readonly ?string $methodId = null,
        public readonly ?string $type = null
    ) {
    }
}
