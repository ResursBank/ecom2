<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

use function preg_match;

/**
 * Used for UUID validation.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsUuid
{
    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        if (
            !preg_match(
                pattern: '/^[\da-f]{8}-[\da-f]{4}-[0-5][\da-f]{3}-[\da-d][\da-f]{3}-[\da-f]{12}$/i',
                subject: $value
            )
        ) {
            throw new IllegalValueException(
                message: $name . ' value ' . $value . ' is not a UUID.'
            );
        }
    }
}
