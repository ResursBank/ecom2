<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Information about the identification made on a payment.
 */
class Identification extends Model
{
    /**
     * @param string $type
     * @param string $reference
     */
    public function __construct(
        public readonly string $type,
        public readonly string $reference = '',
    ) {
    }
}
