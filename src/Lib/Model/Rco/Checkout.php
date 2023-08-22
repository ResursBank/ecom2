<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;

/**
 * Implementation of CheckoutDto object.
 *
 * This is similar to CreateCheckout but not the same, this object it utilized
 * for responses from the API when fetching the session.
 */
class Checkout extends Model
{
    /**
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        #[StringNotEmpty] #[StringIsUuid] public readonly string $id,
        #[StringNotEmpty] #[StringIsUuid] public readonly string $storeId,
        #[StringNotEmpty] #[StringMatchesRegex(pattern: '/^[a-zA-Z0-9]{1,32}$/')]
        public readonly string $orderReference,
        public readonly CountryCode $countryCode,
        public readonly Locale $locale,
        public readonly Currency $currency,
        #[StringNotEmpty] #[StringIsUuid] public readonly string $version,
        public readonly Options $options,
        public readonly Customer $customer,
        public readonly Status $status,
        public readonly ?Cart $cart = null,
        public readonly ?Shipping $shipping = null,
        public readonly ?PaymentMethods $paymentMethods = null,
        public readonly ?PspPayment $payment = null,
        public readonly ?Merchant $merchant = null,
        public readonly ?CheckboxCollection $checkboxes = null,
        public readonly ?string $notes = null,
    ) {
        parent::__construct();
    }
}
