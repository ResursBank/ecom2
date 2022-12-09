<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use function strlen;

/**
 * Tax related methods.
 */
class Tax
{
    /**
     * Calculate tax rate based on supplied values.
     *
     * @param float $amount1
     * @param float $amount2
     * @return float
     * @todo Should return Enum since API only accepts specific values. ECP-308
     */
    public static function getRate(float $amount1, float $amount2): float
    {
        return $amount1 === 0.0 || $amount2 === 0.0 ? 0.0 : round(
                num: $amount1 / $amount2,
                precision: 2
            ) * 100;
    }
}
