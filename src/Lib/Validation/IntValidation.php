<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Validation;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;

use function is_int;

/**
 * Methods to validate integers.
 */
class IntValidation
{
    /**
     * Validates the supplied array contains an element named $key and that
     * element contains an integer. Returns the validated integer.
     *
     * @param array $data
     * @param string $key
     * @return int
     * @throws MissingKeyException
     * @throws IllegalTypeException
     */
    public function getKey(array $data, string $key): int
    {
        if (!isset($data[$key])) {
            throw new MissingKeyException(
                message: "Missing $key key in array."
            );
        }

        if (!is_int(value: $data[$key])) {
            throw new IllegalTypeException(
                message: "$key is not an int."
            );
        }

        return $data[$key];
    }

    /**
     * @param int $value
     * @return bool
     * @throws IllegalValueException
     */
    public function isPositive(
        int $value
    ): bool {
        if ($value < 0) {
            throw new IllegalValueException(
                message: "$value may not be negative."
            );
        }

        return true;
    }

    /**
     * @param int $value
     * @param int $min
     * @return bool
     * @throws IllegalValueException
     */
    public function isGt(
        int $value,
        int $min
    ): bool {
        if ($value <= $min) {
            throw new IllegalValueException(
                message: "$value may not be less than or equal to $min."
            );
        }

        return true;
    }
}
