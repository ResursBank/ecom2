<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

use function strlen;

/**
 * Used for setting minimum and maximum lengths on string properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringLength
{
    /**
     * @param int|null $min Minimum string length
     * @param int|null $max Maximum string length
     */
    public function __construct(
        public readonly ?int $min = null,
        public readonly ?int $max = null
    ) {
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        if ($this->min !== null && strlen(string: $value) < $this->min) {
            throw new IllegalValueException(
                message: $name . ' is shorter than its specified minimum length of ' . $this->min
            );
        }

        if ($this->max !== null && strlen(string: $value) > $this->max) {
            throw new IllegalValueException(
                message: $name . ' is longer than its specified minimum length of ' . $this->min
            );
        }
    }
}
