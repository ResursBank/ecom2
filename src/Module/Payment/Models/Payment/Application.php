<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

class Application extends Model
{
    /**
     * @param int $approvedCreditLimit
     * @param float $requestedCreditLimit
     * @param int $reference Credit application reference (int64).
     */
    public function __construct(
        public readonly int $approvedCreditLimit,
        public readonly float $requestedCreditLimit,
        public readonly int $reference,
    ) {
    }
}
