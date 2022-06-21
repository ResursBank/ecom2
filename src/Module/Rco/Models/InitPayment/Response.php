<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\InitPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines an InitPayment response object
 */
class Response extends Model
{
    public function __construct(
        public readonly string $paymentSessionId,
        public readonly string $iframe,
        public readonly string $script,
        public readonly Customer $customer,
        public readonly string $baseUrl,
        public readonly string $html
    ) {
    }
}
