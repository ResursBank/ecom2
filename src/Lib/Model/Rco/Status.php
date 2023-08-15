<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Model\Rco\Status\Location;

/**
 * Implementation of StatusDto object.
 */
class Status extends Model
{
    public function __construct(
        public readonly CheckoutStatus $type,
        public readonly ?Location $location = null
    ) {
    }
}
