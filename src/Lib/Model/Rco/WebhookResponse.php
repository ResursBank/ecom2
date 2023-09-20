<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;

/**
 * Implementation of WebhookResponseDto object.
 */
class WebhookResponse extends Model
{
    public function __construct(
        public readonly ?Recipient $delivery,
        public readonly ?Recipient $billing,
        public readonly ?CreateShippingMethodCollection $shippingMethods,
        public readonly ?string $orderReference,
        public readonly ?ItemCollection $cartItems
    ) {
        parent::__construct();
    }
}
