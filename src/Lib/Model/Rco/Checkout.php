<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Cart;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Checkboxes;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Options;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Redirects;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Shipping;
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
        public readonly Cart $cart,
        public readonly Merchant $merchant,
        public readonly ?Customer $customer = null,
        public readonly ?Currency $currency = null,
        public readonly ?string $orderReference = null,
        public readonly ?Options $options = null,
        public readonly ?Locale $locale = null,
        public readonly ?Shipping $shipping = null,
        public readonly ?string $id = null,
        public readonly ?string $version = null,
        public readonly ?Callbacks $callbacks = null,
        public readonly ?Redirects $redirects = null,
        public readonly ?Webhooks $webhooks = null,
        public readonly ?Checkboxes $checkboxes = null,
        public readonly ?string $notes = null,
        public readonly ?PaymentMethods $paymentMethods = null,
        public readonly ?Payment $payment = null,
        public readonly ?Status $status = null
    ) {
    }
}
