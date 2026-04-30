<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use ReflectionParameter;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\StringInterface;
use Resursbank\Ecom\Lib\Utilities\Strings;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsSwedishSsnOrOrg implements StringInterface
{
    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        if (
            Strings::isSwedishSsn(value: $value) ||
            Strings::isSwedishOrgNo(value: $value)
        ) {
            return;
        }

        throw new IllegalValueException(
            message: $name . ' value ' . $value . ' is not a properly ' .
            'formatted Swedish SSN or org number.'
        );
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getAcceptedValues(ReflectionParameter $parameter, int $size = 5): array
    {
        // @todo: Implement properly
        return array_fill(start_index: 0, count: $size, value: '198305147715');
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getRejectedValues(ReflectionParameter $parameter, int $size = 5): array
    {
        // @todo: Implement properly
        return array_fill(
            start_index: 0,
            count: $size,
            value: '19830dge5147715'
        );
    }
}
