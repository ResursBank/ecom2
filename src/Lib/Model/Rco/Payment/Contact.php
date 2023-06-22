<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer contact information for RCO+.
 */
class Contact extends Model
{
    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     */
    public function __construct(
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $email,
        private readonly string $phone
    ) {
    }
}
