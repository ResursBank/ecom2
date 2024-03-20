<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCallbacks;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCheckboxCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCustomer;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateMerchant;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateRedirects;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateSetStatus;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateWebhooks;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;

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
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly CreateCart $cart,
        public readonly CreateMerchant $merchant,
        #[StringMatchesRegex(pattern: '/^$|^[a-zA-Z0-9]{1,32}$/')]
        public readonly ?string $orderReference = null,
        public readonly ?CreateOptions $options = null,
        public readonly ?CreateCustomer $customer = null,
        public readonly ?CreateRedirects $redirects = null,
        public readonly ?CreateCallbacks $callbacks = null,
        public readonly ?CreateWebhooks $webhooks = null,
        public readonly ?Locale $locale = null,
        public readonly ?Currency $currency = null,
        public readonly ?CreateCheckboxCollection $checkboxes = null,
        public readonly ?CreateSetStatus $initialStatus = null,
        public readonly ?string $selectedPaymentMethodId = null
    ) {
        parent::__construct();
    }
}
