<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;

/**
 * Implementation of SetStatusDto object.
 */
class SetStatus extends Model
{
    public function __construct(
        public readonly ?CheckoutStatus $type,
        public readonly ?string $callingIp
    ) {
    }
}
