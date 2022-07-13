<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Validation;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;

use function is_float;
use function is_int;

/**
 * Methods to validate floats.
 */
class FloatValidation
{
    /**
     * Validates the supplied array contains an element named $key and that
     * element contains a float. Returns the validated float.
     *
     * @param array $data
     * @param string $key
     * @param bool $parseInt
     * @return float
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function getKey(
        array $data,
        string $key,
        bool $parseInt = false
    ): float {
        if (!isset($data[$key])) {
            throw new MissingKeyException(
                message: "Missing $key key in array."
            );
        }

        if (is_int(value : $data[$key]) && $parseInt) {
            $data[$key] = (float) $data[$key];
        }

        if (!is_float(value: $data[$key])) {
            throw new IllegalTypeException(
                message: "$key is not a float."
            );
        }

        return $data[$key];
    }
}
