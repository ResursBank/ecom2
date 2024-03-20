<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CreateWebhooksDto object.
 */
class CreateWebhooks extends Model
{
    public function __construct(
        public readonly ?CreateWebhook $customer = null,
        public readonly ?CreateWebhook $cart = null,
        public readonly ?CreateWebhook $shipping = null,
        public readonly ?CreateWebhook $payment = null,
        public readonly ?CreateWebhook $validate = null
    ) {
        parent::__construct();
    }
}
