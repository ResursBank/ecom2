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
use stdClass;

use function in_array;
use function is_array;

/**
 * Methods to validate arrays.
 */
class ArrayValidation
{
    /**
     * Validates the supplied array contains an element named $key and that
     * element contains an array. Returns the validated array.
     *
     * @param array $data
     * @param string $key
     * @return array
     * @throws MissingKeyException
     * @throws IllegalTypeException
     */
    public function getKey(array $data, string $key): array
    {
        if (!isset($data[$key])) {
            throw new MissingKeyException(
                message: "Missing $key key in array."
            );
        }

        if (!is_array(value: $data[$key])) {
            throw new IllegalTypeException(
                message: "$key is not an array."
            );
        }

        return $data[$key];
    }

    /**
     * Validate supplied array is sequential.
     *
     * @param array $data
     * @return bool
     * @throws IllegalValueException
     */
    public function isSequential(array $data): bool
    {
        $keys = array_keys(array: $data);
        $range = range(start: 0, end: count($data) - 1);

        if ($keys !== $range) {
            throw new IllegalValueException(message: 'Array not sequential.');
        }
        return true;
    }

    /**
     * Validate supplied array is associative.
     *
     * @param array $data
     * @return bool
     * @throws IllegalValueException
     */
    public function isAssoc(array $data): bool
    {
        $keys = array_keys(array: $data);
        $range = range(start: 0, end: count($data) - 1);

        if ($keys === $range) {
            throw new IllegalValueException(message: 'Array is sequential.');
        }

        return true;
    }

    /**
     * Validate depth of multidimensional array.
     *
     * @param array $data
     * @param int $depth
     * @return bool
     * @throws IllegalTypeException
     */
    public function isMultiDimensional(array $data, int $depth): bool
    {
        if ($depth > 0) {
            foreach ($data as $el) {
                if (!is_array(value: $el)) {
                    throw new IllegalTypeException(
                        message: 'Array contains none array element.'
                    );
                }

                if ($depth - 1 > 0) {
                    $this->isMultiDimensional(data: $el, depth: $depth - 1);
                }
            }
        }

        return true;
    }

    /**
     * Validate one-dimensional array contains only stdClass instances.
     *
     * @param array $data
     * @return bool
     * @throws IllegalTypeException
     */
    public function isStdClassCollection(
        array $data
    ): bool {
        foreach ($data as $item) {
            if (!$item instanceof stdClass) {
                throw new IllegalTypeException(
                    message: 'Array contains data that is not an stdClass ' .
                        'instance.'
                );
            }
        }

        return true;
    }

    /**
     * Ensure array only defines keys in $allowed.
     *
     * @param array $data
     * @param array $allowed
     * @return bool
     * @throws IllegalValueException
     */
    public function allowedKeys(array $data, array $allowed): bool
    {
        foreach (array_keys(array: $data) as $key) {
            if (!in_array(needle: $key, haystack: $allowed, strict: true)) {
                throw new IllegalValueException(
                    message: 'Array contains illegal key.'
                );
            }
        }

        return true;
    }
}
