<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCheckboxCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of CreateCheckoutDto object.
 *
 * Similar too Checkout model but not the same. This model is utilized when
 * creating a new checkout session and include unique properties to apply local
 * endpoints Resurs Bank can interact with (callbacks, webhooks, redirects).
 */
class CreateCheckout extends Model
{
    /**
     * @throws IllegalCharsetException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly CreateCart $cart,
        public readonly Merchant $merchant,
        public readonly ?string $orderReference = null,
        public readonly ?CreateOptions $options = null,
        public readonly ?Customer $customer = null,
        public readonly ?Redirects $redirects = null,
        public readonly ?Callbacks $callbacks = null,
        public readonly ?Webhooks $webhooks = null,
        public readonly ?Locale $locale = null,
        public readonly ?Currency $currency = null,
        public readonly ?CreateCheckboxCollection $checkboxes = null,
        public readonly ?SetStatus $initialStatus = null,
        public readonly ?string $selectedPaymentMethodId = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateOrderReference();
    }

    /**
     * @throws IllegalCharsetException
     */
    public function validateOrderReference(): void
    {
        if ($this->orderReference === null) {
            return;
        }

        $this->stringValidation->matchRegex(
            value: $this->orderReference,
            pattern: '/^$|^[a-zA-Z0-9]{1,32}$/'
        );
    }
}
