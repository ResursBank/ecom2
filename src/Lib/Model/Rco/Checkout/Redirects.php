<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Exception\UrlValidationException;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Redirects model for RCO+ (similar to the older flows success- and failUrl).
 */
class Redirects extends Model
{
    /**
     * Urls must be given in https format.
     *
     * @param string|null $checkout Fail/cancel url..
     * @param string|null $success Successful payment url.
     * @throws UrlValidationException
     */
    public function __construct(
        public readonly ?string $checkout = null,
        public readonly ?string $success = null
    ) {
        $this->validateCheckoutUrl();
        $this->validateSuccessUrl();
    }

    /**
     * @throws UrlValidationException
     */
    private function validateCheckoutUrl(): void
    {
        if (!$this->checkout) {
            return;
        }

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
        if (!$this->success) {
            return;
        }

        if (!filter_var(value: $this->success, filter: FILTER_VALIDATE_URL)) {
            throw new UrlValidationException(message: 'Invalid redirect URL.');
        }
    }
}
