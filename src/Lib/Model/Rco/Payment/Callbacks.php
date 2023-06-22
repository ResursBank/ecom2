<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Authorized;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Cancelled;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Captured;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Created;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Failed;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Paid;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Refunded;

/**
 * Main callback model for RCO+.
 */
class Callbacks extends Model
{
    /**
     * @param Paid $paid Callback called when a checkout has been completed and paid for.
     * @param Created $created Callback called when a payment has been created
     * @param Failed $failed Callback called when a payment has failed.
     * @param Authorized $authorized Callback called when a payment has been authorized.
     * @param Captured $captured Callback called when a payment has been authorized.
     * @param Cancelled $cancelled Callback called when a payment has been cancelled
     * @param Refunded $refunded Callback called when a payment has been refunded
     */
    public function __construct(
        public readonly Paid $paid,
        public readonly Created $created,
        public readonly Failed $failed,
        public readonly Authorized $authorized,
        public readonly Captured $captured,
        public readonly Cancelled $cancelled,
        public readonly Refunded $refunded
    ) {
    }
}
