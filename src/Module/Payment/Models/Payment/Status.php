<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Status class for payment statuses.
 */
class Status extends Model
{
    /**
     * @param string $value TASK_REDIRECTION_REQUIRED, INSPECTION, SUPPLEMENTING_REQUIRED, FROZEN, ACCEPTED, REJECTED
     * @param array $possibleActions
     * @param float $totalOrderAmount
     * @param float $canceledAmount
     * @param float $capturedAmount
     * @param float $refundedAmount
     * @param float $authorizedAmount
     *
     * @todo authorizedAmount has not been present in the tests, but are shown in swagger example.
     * @todo We need to make sure whether authorizedAmount should be there or not.
     */
    public function __construct(
        public readonly string $value,
        public readonly array $possibleActions,
        public readonly float $totalOrderAmount = 0.00,
        public readonly float $canceledAmount = 0.00,
        public readonly float $capturedAmount = 0.00,
        public readonly float $refundedAmount = 0.00,
        public readonly float $authorizedAmount = 0.00,
    ) {
    }
}
