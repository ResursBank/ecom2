<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Authorized;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Cancelled;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Captured;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Created;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Failed;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Paid;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Refunded;

class Callbacks extends Model
{
    public function __construct(
        public readonly Authorized $authorized,
        public readonly Cancelled $cancelled,
        public readonly Captured $captured,
        public readonly Created $created,
        public readonly Failed $failed,
        public readonly Paid $paid,
        public readonly Refunded $refunded
    ) {
    }
}
