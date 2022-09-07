<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Order;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Payment\Models\Order\ActionLog\OrderLineCollection;

class ActionLog extends Model
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $created,
        public readonly string $creator,
        public readonly OrderLineCollection $orderLines,
        public readonly ?string $transactionId = null
    ) {
    }
}
