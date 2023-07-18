<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethods\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethods\Selection;

/**
 * Defines the PaymentMethods property of a Checkout.
 */
class PaymentMethods extends Model
{
    public function __construct(
        public readonly ?Selection $selection = null,
        public readonly ?PaymentMethodCollection $methods = null
    ) {
    }
}
