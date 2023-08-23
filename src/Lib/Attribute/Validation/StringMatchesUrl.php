<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;

use function preg_match;

/**
 * Used for regex validation of urls.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringMatchesUrl
{
    /**
     * @throws IllegalUrlException
     */
    public function validate(string $name, string $value): void
    {
        if (
            !preg_match(
                pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/',
                subject: $value
            )
        ) {
            throw new IllegalUrlException(
                message: $name . ' value ' . $value . ' is not an URL'
            );
        }
    }
}
