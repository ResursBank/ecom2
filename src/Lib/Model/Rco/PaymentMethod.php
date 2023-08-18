<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\LinkCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;

/**
 * Implementation of PaymentMethodDto object.
 */
class PaymentMethod extends Model
{
    public function __construct(
        public readonly string $methodId,
        public readonly string $name,
        public readonly Type $type,
        public readonly int $fee,
        public readonly RequiredCollection $required,
        public readonly string $subtitle,
        public readonly array $descriptions,
        public readonly string $terms,
        public readonly LinkCollection $links
    ) {
    }
}
