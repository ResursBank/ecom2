<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Exception\UrlValidationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Redirects model for RCO+ (similar to the older flows success- and failUrl).
 */
class Redirects extends Model
{
    /**
     * Urls must be given in https format.
     *
     * @param string $success Successful payment url. If left empty, the default status page will be shown.
     * @param string $checkout Fail/cancel url. Url takes user back to the store which must load the checkout again.
     * @throws EmptyValueException
     * @throws UrlValidationException
     */
    public function __construct(
        public readonly string $checkout,
        public readonly string $success = '',
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateCheckoutUrl();
        $this->validateSuccessUrl();
    }

    /**
     * @throws UrlValidationException
     * @throws EmptyValueException
     */
    private function validateCheckoutUrl(): void
    {
        $this->stringValidation->notEmpty(value: $this->checkout);

        if (!filter_var(value: $this->checkout, filter: FILTER_VALIDATE_URL)) {
            throw new UrlValidationException(message: 'Invalid redirect URL.');
        }
    }

    /**
     * Make sure the successUrl is a valid URL.
     *
     * @throws UrlValidationException
     */
    private function validateSuccessUrl(): void
    {
        if ($this->success === '') {
            return;
        }

        if (!filter_var(value: $this->success, filter: FILTER_VALIDATE_URL)) {
            throw new UrlValidationException(message: 'Invalid redirect URL.');
        }
    }
}
