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
     * Urls must be given in https format.
     *
     * @param string $success Successful payment url. If left empty, the default status page will be shown.
     * @param string $checkout Fail/cancel url. Url takes user back to the store which must load the checkout again.
     * @todo Validate URLs as URLs.
     */
    public function __construct(
        public readonly string $success,
        public readonly string $checkout
    ) {
    }
}
