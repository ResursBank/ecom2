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
 * Used for validating ISO 8601 formatted dates.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsDatetime
{
    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        $pattern = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d' .
            '|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|' .
            '[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]' .
            '\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|(' .
            '[\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

        if (preg_match(pattern: $pattern, subject: $value)) {
            return;
        }

        throw new IllegalValueException(
            message: $name . ' value ' . $value . ' is not a valid date'
        );
    }
}
