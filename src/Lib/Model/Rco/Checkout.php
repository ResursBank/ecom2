<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Callbacks;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Cart;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Checkboxes;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Options;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Redirects;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Webhooks;

/**
 * RCO+ Payment Model.
 */
class Checkout extends Model
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $orderReference,
        public readonly Options $options,
        public readonly Locale $locale,
        public readonly Currency $currency,
        public readonly Cart $cart,
        public readonly Customer $customer,
        public readonly Checkboxes $checkboxes,
        public readonly Merchant $merchant,
        public readonly ?string $id = null,
        public readonly ?Callbacks $callbacks = null,
        public readonly ?Redirects $redirects = null,
        public readonly ?Webhooks $webhooks = null
    ) {
    }
}
