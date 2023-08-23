<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;

use function is_string;

/**
 * Used for validating that an array only contains strings.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ArrayOfStrings
{
    /**
     * @throws IllegalTypeException
     */
    public function validate(string $name, array $value): void
    {
        foreach ($value as $key => $element) {
            if (!is_string(value: $element)) {
                throw new IllegalTypeException(
                    message: 'Array ' . $name . ' contains data that is not ' .
                        'of type string at index ' . $key . '.'
                );
            }
        }
    }
}
