<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\InitPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines an OrderLine object as used when creating a new payment request
 */
class OrderLine extends Model
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
