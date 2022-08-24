<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

class Status
{
    /**
     * @param string $value TASK_REDIRECTION_REQUIRED, INSPECTION, SUPPLEMENTING_REQUIRED, FROZEN, ACCEPTED, REJECTED
     * @param array $possibleActions
     * @param float $totalOrderAmount
     * @param float $canceledAmount
     * @param float $capturedAmount
     * @param float $refundedAmount
     * @param float $authorizedAmount
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
