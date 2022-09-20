<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;

/**
 * Defines an action log item
 */
class ActionLog extends Model
{
    /**
     * @param string $actionId
     * @param string $type
     * @param string $created
     * @param OrderLineCollection $orderLines
     * @param string|null $transactionId
     * @param string|null $creator
     */
    public function __construct(
        public readonly string $actionId,
        public readonly string $type,
        public readonly string $created,
        public readonly OrderLineCollection $orderLines,
        public readonly ?string $transactionId = null,
        public readonly ?string $creator = null,
    ) {
    }
}
