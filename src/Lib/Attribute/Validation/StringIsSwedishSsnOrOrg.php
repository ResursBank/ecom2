<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\Strings;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsSwedishSsnOrOrg
{
    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        if (!Strings::isSwedishOrgNo(value: $value)) {
            throw new IllegalValueException(
                message: $name . ' value ' . $value . ' is not a properly ' .
                'formatted Swedish SSN.'
            );
        }

        if (!Strings::isSwedishOrgNo(value: $value)) {
            throw new IllegalValueException(
                message: $name . ' value ' . $value . ' is not a properly ' .
                'formatted Swedish org. number.'
            );
        }
    }
}
