<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Exception;
use Random\RandomException;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\FloatInterface;

/**
 * Used for setting minimum and maximum value on float properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class FloatValue implements FloatInterface
{
    /**
     * @param float|null $min Minimum value
     * @param float|null $max Maximum value
     * @throws AttributeParameterException
     */
    public function __construct(
        public readonly ?float $min = null,
        public readonly ?float $max = null,
        public readonly int $precision = 2
    ) {
        if ($min !== null && $max !== null && $min > $max) {
            throw new AttributeParameterException(
                message: 'Attribute min parameter value (' .
                $min . ') is greater than max parameter value (' . $max . ')!'
            );
        }
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, float $value): void
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

        // Confirm value contains no more decimals than allowed by specified precision.
        if (str_contains((string) $value, '.')) {
            $decimals = strlen(explode('.', (string) $value)[1]);

            if ($decimals > $this->precision) {
                throw new IllegalValueException(
                    message: 'Value of ' . $name . ' contains more decimals than specified precision of ' . $this->precision
                );
            }
        }
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $size = max($size, 1);

        $result = [];

        // Add threshold values.
        if ($this->min !== null) {
            $result[] = $this->min;
        }

        if ($this->max !== null) {
            $result[] = $this->max;
        }

        /* Resolve default values to generate random values (no min / max = all
           values are allowed). */
        $min = ($this->min ?? -999999999 + $this->getRandomDecimal());
        $max = ($this->max ?? 999999999 + $this->getRandomDecimal());

        // Add random values.
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->getRandomValue(min: $min, max: $max);
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $size = max($size, 2);

        $result = [];

        // Add threshold values.
        if ($this->min !== null) {
            $result[] = $this->min - 0.01;
        }

        if ($this->max !== null) {
            $result[] = $this->max + 0.01;
        }

        if ($this->min === null && $this->max === null) {
            return $result;
        }

        $this->addRandomRejectedValues(result: $result, size: $size);

        return $result;
    }

    /**
     * Resolve random float value between min and max.
     *
     * @param float $min
     * @param float $max
     * @param int|null $precision
     * @return float
     * @throws RandomException
     */
    public function getRandomValue(float $min, float $max, ?int $precision = null): float
    {
        $result = random_int((int) $min, (int) $max) + $this->getRandomDecimal();

        if ($result > $max) {
            $result = $max;
        }

        return round($result, $precision ?? $this->precision);
    }

    /**
     * Generate a value between 0.00 and 0.99
     *
     * @return float
     */
    public function getRandomDecimal(): float
    {
        return round(mt_rand() / mt_getrandmax(), $this->precision);
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
        int $size
    ): void {
        $count = 0;

        // Generate random values.
        while ($count < $size) {
            // Generate values above max.
            if ($this->max !== null) {
                $result[] = $this->getRandomValue(
                    min: $this->max + 1,
                    max: $this->max + 999999
                );
                $count++;
            }

            if ($this->min === null) {
                continue;
            }

            // Generate values below min.
            $result[] = $this->getRandomValue(
                min: $this->min - 999999,
                max: $this->min - 1
            );
            $count++;

            // Generate values exceeding precision.
            $result[] = $this->getRandomValue(
                min: $this->min,
                max: $this->max,
                precision: $this->precision + 1
            );
        }
    }
}
