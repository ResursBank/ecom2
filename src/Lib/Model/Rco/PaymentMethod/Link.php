<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of PaymentMethodLinkDto object.
 */
class Link extends Model
{
    public function __construct(
        public readonly string $label,
        public readonly string $url
    ) {
    }
}
