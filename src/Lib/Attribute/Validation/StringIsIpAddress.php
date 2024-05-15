<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalIpException;

use function filter_var;

use const FILTER_VALIDATE_IP;

/**
 * Used for validation of IP addresses.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsIpAddress
{
    /**
     * Validates the given IP address.
     *
     * @throws IllegalIpException
     */
    public function validate(string $name, string $value): void
    {
        if (
            $value !== '' &&
            !filter_var(value: $value, filter: FILTER_VALIDATE_IP)
        ) {
            throw new IllegalIpException(
                message: $name . ' value ' . $value . ' is not a valid IP address'
            );
        }
    }
}
