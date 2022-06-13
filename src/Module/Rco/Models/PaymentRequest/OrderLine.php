<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\PaymentRequest;

/**
 * Defines an OrderLine object as used when creating a new payment request
 */
class OrderLine
{
    public function __construct(
        public string $artNo,
        public string $description,
        public float $quantity,
        public string $unitMeasure,
        public float $unitAmountWithoutVat,
        public float $vatPct,
        public ?float $totalAmountWithVat = null,
        public ?float $totalVatAmount = null
    ) {
    }
}
