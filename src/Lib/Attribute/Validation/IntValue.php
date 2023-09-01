<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

/**
 * Used for setting minimum and maximum value on int properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class IntValue
{
    /**
     * @param int|null $min Minimum value
     * @param int|null $max Maximum value
     */
    public function __construct(
        public readonly ?int $min = null,
        public readonly ?int $max = null
    ) {
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, int $value): void
    {
        if (isset($this->min) && $value < $this->min) {
            throw new IllegalValueException(
                message: 'Value of ' . $name . ' is less than its specified minimum value of ' . $this->min
            );
        }

        if (isset($this->max) && $value > $this->max) {
            throw new IllegalValueException(
                message: 'Value of ' . $name . ' is greater than its specified maximum value of ' . $this->max
            );
        }
    }
}
