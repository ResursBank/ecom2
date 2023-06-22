<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Redirects model for RCO+ (similar to the older flows success- and failUrl).
 */
class Redirects extends Model
{
    /**
     * @param string $success An https url to redirect to upon successfully payment, if left empty, the default status page will be shown.
     * @param string $checkout A https url to redirect to upon failed payment or if the user cancels the payment flow. This url must take the user back to the store which must load the checkout again.
     */
    public function __construct(
        public readonly string $success,
        public readonly string $checkout
    ) {
    }
}
