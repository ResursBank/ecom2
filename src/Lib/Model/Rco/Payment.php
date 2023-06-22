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
use Resursbank\Ecom\Lib\Model\Rco\Payment\Cart;
use Resursbank\Ecom\Lib\Model\Rco\Payment\Checkboxes;
use Resursbank\Ecom\Lib\Model\Rco\Payment\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Payment\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Payment\Options;
use Resursbank\Ecom\Lib\Model\Rco\Payment\Redirects;
use Resursbank\Ecom\Lib\Model\Rco\Payment\Webhooks;
use Resursbank\Ecom\Lib\Model\Rco\Webhooks\WebhookModel;

/**
 * RCO+ Payment Model.
 */
class Payment extends Model
{
    /**
     * @param Options $options
     * @param string $orderReference
     * @param Locale $locale
     * @param Currency $currency
     * @param Cart $cart
     * @param Customer $customer
     * @param Redirects $redirects
     * @param Callbacks $callbacks
     * @param Webhooks $webhooks
     * @param Checkboxes $checkboxes
     * @param Merchant $merchant
     */
    public function __construct(
        private readonly Options $options,
        private readonly string $orderReference,
        private readonly Locale $locale,
        private readonly Currency $currency,
        private readonly Cart $cart,
        private readonly Customer $customer,
        private readonly Redirects $redirects,
        private readonly Callbacks $callbacks,
        private readonly Webhooks $webhooks,
        private readonly Checkboxes $checkboxes,
        private readonly Merchant $merchant,
    ) {
    }
}
