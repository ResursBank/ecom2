<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Collection\Collection;

use function count;

/**
 * Used for setting minimum and maximum size of Collection properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class CollectionSize
{
    /**
     * @param int $min Minimum number of Collection elements
     * @param int|null $max Maximum number of Collection elements
     */
    public function __construct(
        public readonly int $min = 0,
        public readonly ?int $max = null
    ) {
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, Collection $value): void
    {
        if (count($value) < $this->min) {
            throw new IllegalValueException(
                message: 'Argument ' . $name . ' contains ' . count($value) .
                ' elements, minimum of ' . $this->min . ' required.'
            );
        }

        if ($this->max !== null && count($value) > $this->max) {
            throw new IllegalValueException(
                message: 'Argument ' . $name . ' contains ' . count($value) .
                ' elements, maximum of ' . $this->max . ' allowed.'
            );
        }
    }
}
