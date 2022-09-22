<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use Resursbank\Ecom\Lib\Model\Model;

class PossibleAction extends Model
{
    public function __construct(
        public readonly \Resursbank\Ecom\Module\Payment\Enum\PossibleAction $action
    ) {
    }
}