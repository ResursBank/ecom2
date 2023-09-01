<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Exception;
use ReflectionParameter;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\ArrayInterface;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\Ecom\Lib\Utilities\Random\DataType;

use function is_string;

/**
 * Used for validating that an array only contains strings.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ArrayOfStrings implements ArrayInterface
{
    /**
     * @throws IllegalTypeException
     */
    public function validate(string $name, array $value): void
    {
        foreach ($value as $key => $element) {
            if (!is_string(value: $element)) {
                throw new IllegalTypeException(
                    message: 'Array ' . $name . ' contains data that is not ' .
                        'of type string at index ' . $key . '.'
                );
            }
        }
    }

    /**
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $result = [];

        // Extract min / max size from combined ArraySize attribute.
        $min = $this->getSizeAttribute(parameter: $parameter)?->min;
        $max = $this->getSizeAttribute(parameter: $parameter)?->max;

        // Add threshold values.
        if ((int) $min > 0) {
            $this->getRandom(min: (int) $min, max: (int) $min);
        } else {
            $result[] = [];
        }

        if ($max !== null) {
            $this->getRandom(min: $max, max: $max);
        }

        // Add randomized values.
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->getRandom(min: $min ?? 0, max: $max ?? 100);
        }

        return $result;
    }

    /**
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $result = [];

        // Extract min / max size from combined ArraySize attribute.
        $min = $this->getSizeAttribute(parameter: $parameter)?->min;
        $max = $this->getSizeAttribute(parameter: $parameter)?->max;

        // Add threshold values.
        if ($min !== null) {
            $this->getRandom(min: $min - 1, max: $min - 1);
        } else {
            $result[] = [];
        }

        if ($max !== null) {
            $this->getRandom(min: $max + 1, max: $max + 1);
        }

        // Add randomized values.
        $this->addRandomRejectedValues(
            result: $result,
            size: $size,
            min: (int) $min,
            max: $max
        );

        return $result;
    }

    /**
     * @throws Exception
     */
    public function getRandom(
        int $min,
        int $max
    ): array {
        $result = [];
        $count = random_int(min: $min, max: $max);

        for ($i = 0; $i < $count; $i++) {
            $result[] = Random::getTypeValue(type: DataType::STRING);
        }

        return $result;
    }

    /**
     * Resolve attribute ArraySize from ReflectionParameter if defined.
     */
    public function getSizeAttribute(
        ReflectionParameter $parameter
    ): ?ArraySize {
        $attributes = $parameter->getAttributes(name: ArraySize::class);

        return isset($attributes[0]) ? $attributes[0]->newInstance() : null;
    }

    /**
     * Append randomized values which will be rejected by property validation.
     *
     * Note: cannot generate rejected random values without min / max (no min
     * / max = all values are allowed).
     *
     * @throws Exception
     */
    private function addRandomRejectedValues(
        array &$result,
        int $size,
        int $min,
        ?int $max
    ): void {
        $count = 0;

        // Generate random values.
        while ($count < $size) {
            if ($max !== null) {
                $result[] = $this->getRandom(min: $max + 1, max: $max + 49);
                $count++;
            }

            if ($min <= 0) {
                continue;
            }

            $result[] = $this->getRandom(min: 0, max: $min - 1);
            $count++;
        }
    }
}
