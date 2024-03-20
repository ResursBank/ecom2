<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of PaymentDto
 */
class Payment extends Model
{
    public function __construct(
        public readonly PaymentMethodCollection $methods,
        public readonly PaymentSelection $selection,
        public readonly PaymentStatus $status
    ) {
        parent::__construct();
    }
}
