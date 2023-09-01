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
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\StringInterface;
use Resursbank\Ecom\Lib\Utilities\Strings;

use function strlen;

/**
 * Used for setting minimum and maximum lengths on string properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringLength implements StringInterface
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
     * Check that the supplied string $value is no shorter than $this->>min and
     * no longer than $this->>max.
     *
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

    /**
     * @inheritDoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $size = max($size, 1);

        $result = [];

        // Add threshold values.
        if ($this->min !== null) {
            $result[] = Strings::generateRandomString(length: $this->min);
        } else {
            $result[] = '';
        }

        if ($this->max !== null) {
            $result[] = Strings::generateRandomString(length: $this->max);
        }

        // Add random values.
        $min = ($this->min ?? 0);
        $max = ($this->max ?? 9999);

        for ($i = 0; $i < $size; $i++) {
            $result[] = Strings::generateRandomString(
                length: random_int(min: $min, max: $max)
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        // Add at least one random min and one max that will be rejected.
        $size = max($size, 2);

        $result = [];

        // Add threshold values.
        if ($this->min !== null && $this->min > 0) {
            $result[] = Strings::generateRandomString(length: $this->min - 1);
        }

        if ($this->max !== null) {
            $result[] = Strings::generateRandomString(length: $this->max + 1);
        }

        // Must have either min or max value to generate values.
        if ($this->min === null && $this->max === null) {
            return $result;
        }

        $this->addRandomRejectedValues(result: $result, size: $size);

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
        int $size
    ): void {
        $count = 0;

        // Generate random values.
        while ($count < $size) {
            if ($this->max !== null) {
                $result[] = Strings::generateRandomString(
                    length: random_int(
                        min: $this->max + 1,
                        max: $this->max + 9999
                    )
                );
                $count++;
            }

            if ((int) $this->min <= 0) {
                continue;
            }

            $result[] = Strings::generateRandomString(
                length: random_int(min: 0, max: $this->min - 1)
            );
            $count++;
        }
    }
}
