<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\LinkCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;

use function in_array;
use function is_string;

/**
 * Implementation of PaymentMethodDto object.
 */
class PaymentMethod extends Model
{
    /**
     * @param array $required This is actually an array of enum values, see
     * ECP-546, currently fixed using evaluateFields to convert data.
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $methodId,
        public readonly string $name,
        public readonly Type $type,
        public readonly int $fee,
        public array $required,
        public readonly string $subtitle,
        public readonly array $descriptions,
        public readonly string $terms,
        public readonly LinkCollection $links,
        private readonly ArrayValidation $arrayValidation = new ArrayValidation()
    ) {
        $this->evaluateRequired();
        $this->validateDescriptions();
    }

    /**
     * Convert anonymous strings to enum correspondent for required.
     */
    private function evaluateRequired(): void
    {
        $data = [];

        foreach ($this->required as $field) {
            $data[] = in_array(
                needle: $field,
                haystack: Required::cases(),
                strict: true
            ) ? $field : Required::from(value: $field);
        }

        $this->required = $data;
    }

    /**
     * @throws IllegalTypeException
     */
    private function validateDescriptions(): void
    {
        $this->arrayValidation->isOfType(
            data: $this->descriptions,
            type: 'string',
            compareFn: static fn (mixed $value) => is_string(value: $value)
        );
    }
}
