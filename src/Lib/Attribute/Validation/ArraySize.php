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
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\ArrayInterface;
use Resursbank\Ecom\Lib\Utilities\Random;

use function count;

/**
 * Used for setting minimum and maximum size of array properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ArraySize implements ArrayInterface
{
    /**
     * @param int $min Minimum number of array elements
     * @param int|null $max Maximum number of array elements
     * @throws AttributeParameterException
     */
    public function __construct(
        public readonly int $min = 0,
        public readonly ?int $max = null
    ) {
        if ($min > $max) {
            throw new AttributeParameterException(
                message: 'Attribute min parameter value (' .
                $min . ') is greater than max parameter value (' . $max . ')!'
            );
        }

        if ($min < 0) {
            throw new AttributeParameterException(
                message: 'Array size min and max values must both be ' .
                'positive, found min = ' . $min . ' and max = ' . $max . '!'
            );
        }
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, array $value): void
    {
        if ($this->min !== null && count($value) < $this->min) {
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

    /**
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $result = [];

        // Add threshold values.
        if ((int) $this->min > 0) {
            $result[] = $this->getRandom(
                min: (int) $this->min,
                max: (int) $this->min
            );
        } else {
            $result[] = [];
        }

        if ($this->max !== null) {
            $result[] = $this->getRandom(min: $this->max, max: $this->max);
        }

        // Add randomized values.
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->getRandom(
                min: $this->min ?? 0,
                max: $this->max ?? 100
            );
        }

        return $result;
    }

    /**
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $result = [];

        // Add threshold values.
        if ($this->min > 0) {
            $result[] = $this->getRandom(
                min: $this->min - 1,
                max: $this->min - 1
            );
        }

        if ($this->max !== null) {
            $result[] = $this->getRandom(
                min: $this->max + 1,
                max: $this->max + 1
            );
        }

        /// Add randomized values.
        $this->addRandomRejectedValues(
            result: $result,
            size: $size,
            min: (int) $this->min,
            max: $this->max
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
            $result[] = Random::getValue();
        }

        return $result;
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
