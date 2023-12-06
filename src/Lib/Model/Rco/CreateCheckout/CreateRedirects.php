<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;

use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CreateRedirectsDto object.
 */
class CreateRedirects extends Model
{
    public function __construct(
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+&@#\/%=~_|]{1,100}/'
        )]
        public readonly ?string $checkout = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+&@#\/%=~_|]{1,100}/'
        )]
        public readonly ?string $success = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+&@#\/%=~_|]{1,100}/'
        )]
        public readonly ?string $failure = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+&@#\/%=~_|]{1,100}/'
        )]
        public readonly ?string $cancel = null
    ) {
        parent::__construct();
    }
}
