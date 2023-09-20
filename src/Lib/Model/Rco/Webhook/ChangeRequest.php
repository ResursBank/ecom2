<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Webhook;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;

/**
 * Implementation of WebhookChangeRequestDto object.
 */
class ChangeRequest extends Model
{
    public function __construct(
        public readonly ?Recipient $delivery,
        public readonly ?Recipient $billing,
        public readonly ?CreateShippingMethodCollection $shippingMethods,
        public readonly ?string $cartCode,
        public readonly ?string $orderReference,
        public readonly ?ItemCollection $cartItems
    ) {
        parent::__construct();
    }
}
