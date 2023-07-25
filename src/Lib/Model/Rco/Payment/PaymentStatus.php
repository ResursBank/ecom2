<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Payment status
 */
class PaymentStatus extends Model
{
    public function __construct(
        public readonly ?int $requestedAmount = null,
        public readonly ?int $authorizedAmount = null,
        public readonly ?int $cancelledAmount = null,
        public readonly ?int $capturedAmount = null,
        public readonly ?int $refundedAmount = null,
        public readonly ?array $availableActions = null,
        public readonly ?string $status = null
    ) {
    }
}
