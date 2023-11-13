<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActionsCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus as PaymentStatusEnum;

/**
 * Implementation of PaymentStatusDto object.
 */
class PaymentStatus extends Model
{
    public function __construct(
        public readonly int $requestedAmount,
        public readonly int $authorizedAmount,
        public readonly int $cancelledAmount,
        public readonly int $capturedAmount,
        public readonly int $refundedAmount,
        public readonly ?AvailableActionsCollection $availableActions = null,
        public readonly ?PaymentStatusEnum $type = null
    ) {
        parent::__construct();
    }
}
