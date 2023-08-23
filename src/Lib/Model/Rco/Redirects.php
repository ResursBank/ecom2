<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of RedirectsDto object.
 */
class Redirects extends Model
{
    public function __construct(
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+?&@#\/%=~_|]{0,100}/'
        )]
        public readonly ?string $checkout = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+?&@#\/%=~_|]{0,100}/'
        )]
        public readonly ?string $success = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+?&@#\/%=~_|]{0,100}/'
        )]
        public readonly ?string $failure = null,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.]{1,100}([{]checkoutId})?[-a-zA-Z0-9+?&@#\/%=~_|]{0,100}/'
        )]
        public readonly ?string $cancel = null
    ) {
        parent::__construct();
    }
}
