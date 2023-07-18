<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Status;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Checkout location information
 */
class Location extends Model
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?string $url = null,
        public readonly ?string $cancelUrl = null
    ) {
    }
}
