<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use Resursbank\Ecom\Lib\Model\Callback\Enum\Action;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Model for callback request management.
 *
 * @see https://merchant-api.integration.resurs.com/docs/v2/merchant_payments_v2/options#callbacks
 */
class Management extends Model
{
    /**
     * @todo Missing validation of properties.
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly Action $action,
        public readonly string $actionId,
        public readonly string $created
    ) {
    }
}
