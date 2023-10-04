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
        public readonly ?Webhook $customer = null,
        public readonly ?Webhook $cart = null,
        public readonly ?Webhook $shipping = null,
        public readonly ?Webhook $payment = null,
        public readonly ?Webhook $validate = null
    ) {
        parent::__construct();
    }
}
