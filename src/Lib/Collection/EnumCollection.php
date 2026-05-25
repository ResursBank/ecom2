<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Collection;

use BackedEnum;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use ValueError;

use function in_array;
use function is_string;

/**
 * Base collection class.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class EnumCollection extends Collection
{
    /**
     * @throws ValueError
     * @throws IllegalTypeException
     */
    public function __construct(array $data, string $type)
    {
        parent::__construct(
            data: $this->evaluateData(data: $data, type: $type),
            type: $type
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws ValueError
     */
    private function evaluateData(array $data, string $type): array
    {
        if (!is_subclass_of(object_or_class: $type, class: BackedEnum::class)) {
            throw new IllegalTypeException(
                message: 'This collection only accepts backed enums.'
            );
        }

        $newData = [];

        foreach ($data as $v) {
            $newData[] = $this->evaluateValue(value: $v, type: $type);
        }

        return $newData;
    }

    /**
     * @throws IllegalTypeException
     */
    private function evaluateValue(mixed $value, string $type): mixed
    {
        if (
            in_array(
                needle: $value,
                haystack: $type::cases(),
                strict: true
            )
        ) {
            return $value;
        }

        if (is_string(value: $value)) {
            return $type::from(value: $value);
        }

        throw new IllegalTypeException(
            message: "Only strings, or cases from $type, are acceptable."
        );
    }
}
