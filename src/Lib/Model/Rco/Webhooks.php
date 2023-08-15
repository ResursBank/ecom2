<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of WebhooksDto object.
 */
class Webhooks extends Model
{
    public function __construct(
        public readonly Webhook $customer,
        public readonly Webhook $cart,
        public readonly Webhook $shipping,
        public readonly Webhook $payment,
        public readonly Webhook $validate
    ) {
    }
}
