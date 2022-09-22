<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use Resursbank\Ecom\Lib\Collection\Collection;

class PossibleActionCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: PossibleAction::class);
    }
}