<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Application data for a payment.
 */
class ApplicationResponse extends Model
{
    /**
     * @param int $requestedCreditLimit
     * @param int $approvedCreditLimit
     * @param int|null $reference Credit application reference (int64).
     * @param CoApplicant|null $coApplicant
     */
    public function __construct(
        public readonly int $requestedCreditLimit,
        public readonly int $approvedCreditLimit,
        public readonly ?int $reference = null,
        public readonly ?CoApplicant $coApplicant = null,
    ) {
    }
}
