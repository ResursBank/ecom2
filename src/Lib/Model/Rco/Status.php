<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Status\Location;

/**
 * Checkout status
 */
class Status extends Model
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?Location $location = null
    ) {
    }
}
