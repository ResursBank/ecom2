<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\UpdatePayment;

/**
 * Defines an OrderLine object as used when creating a new payment request
 */
class OrderLine
{
    /**
     * @param string $artNo
     * @param string|null $description
     * @param float $quantity
     * @param string $unitMeasure
     * @param float $unitAmountWithoutVat
     * @param float $vatPct
     * @param string|null $type
     */
    public function __construct(
        public string $artNo,
        public ?string $description,
        public float $quantity,
        public string $unitMeasure,
        public float $unitAmountWithoutVat,
        public float $vatPct,
        public ?string $type
    ) {
    }
}
