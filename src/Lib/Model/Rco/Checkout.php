<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Cart\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of CheckoutDto object.
 *
 * This is similar to CreateCheckout but not the same, this object it utilized
 * for responses from the API when fetching the session.
 */
class Checkout extends Model
{
    /**
     * @throws IllegalValueException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * // phpcs:ignore
     */
    public function __construct(
        public readonly string $id,
        public readonly string $storeId,
        public readonly string $orderReference,
        public readonly CountryCode $countryCode,
        public readonly Locale $locale,
        public readonly Currency $currency,
        public readonly string $version,
        public readonly Options $options,
        public readonly Customer $customer,
        public readonly Status $status,
        public readonly Cart $cart = new Cart(
        items: new ItemCollection(data: []),
        code: ''
        ),
        public readonly ?Shipping $shipping = null,
        public readonly ?PaymentMethods $paymentMethods = null,
        public readonly ?PspPayment $payment = null,
        public readonly ?Merchant $merchant = null,
        public readonly ?CheckboxCollection $checkboxes = null,
        public readonly ?string $notes = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateId();
        $this->validateStoreId();
        $this->validateOrderReference();
        $this->validateVersion();
    }

    /**
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    public function validateId(): void
    {
        $this->stringValidation->notEmpty(value: $this->id);
        $this->stringValidation->isUuid(value: $this->id);
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function validateStoreId(): void
    {
        $this->stringValidation->notEmpty(value: $this->storeId);
        $this->stringValidation->isUuid(value: $this->storeId);
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     */
    public function validateOrderReference(): void
    {
        $this->stringValidation->notEmpty(value: $this->orderReference);
        $this->stringValidation->matchRegex(
            value: $this->orderReference,
            pattern: '/^[a-zA-Z0-9]{1,32}$/'
        );
    }

    /**
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    public function validateVersion(): void
    {
        $this->stringValidation->notEmpty(value: $this->version);
        $this->stringValidation->isUuid(value: $this->version);
    }
}
