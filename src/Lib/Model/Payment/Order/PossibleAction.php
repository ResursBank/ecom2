<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Order\Action;

/**
 * Defines a possible action for the order.
 */
class PossibleAction extends Model
{
    /**
     * @param Action|null $action
     */
    public function __construct(
        public readonly ?Action $action = null,
    ) {
    }
}
