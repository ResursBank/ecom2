<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;

use function trim;

/**
 * Used for indicating that a string parameter may not be empty.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringNotEmpty
{
    /**
     * @throws EmptyValueException
     */
    public function validate(string $name, string $value): void
    {
        if (trim(string: $value) === '') {
            throw new EmptyValueException(
                message: 'String value ' . $name . ' cannot be empty.'
            );
        }
    }
}
