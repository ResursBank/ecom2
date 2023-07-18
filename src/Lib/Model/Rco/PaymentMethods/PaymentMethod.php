<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\PaymentMethods;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines a payment method.
 */
class PaymentMethod extends Model
{
    public function __construct(
        public readonly ?string $methodId = null,
        public readonly ?string $name = null,
        public readonly ?string $type = null,
        public readonly ?int $fee = null,
        public readonly ?array $required = null,
        public readonly ?string $subtitle = null,
        public readonly ?array $descriptions = null,
        public readonly ?string $terms = null,
        public readonly ?array $links = null
    ) {
    }
}
