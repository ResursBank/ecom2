<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of RedirectsDto object.
 */
class Redirects extends Model
{
    /**
     * URLs should utilize the https protocol (http can be utilized but
     * everything may not work correctly).
     *
     * @param string|null $checkout
     * @param string|null $success
     * @param string|null $failure
     * @param string|null $cancel
     */
    public function __construct(
        public readonly ?string $checkout = null,
        public readonly ?string $success = null,
        public readonly ?string $failure = null,
        public readonly ?string $cancel = null
    ) {
    }
}
