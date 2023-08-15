<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\PaymentMethods;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines a payment method.
 */
class PaymentMethod extends Model
{
    /**
     * @param array|null $required
     * @param array|null $descriptions
     * @param array|null $links
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly ?string $methodId = null,
        public readonly ?string $name = null,
        public readonly ?string $type = null,
        public readonly ?int $fee = null,
        public readonly ?array $required = null,
        public readonly ?string $subtitle = null,
        public readonly ?array $descriptions = null,
        public readonly ?string $terms = null,
        public readonly ?array $links = null,
        private readonly ArrayValidation $arrayValidation = new ArrayValidation(),
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateDescriptions();
    }

    /**
     * Validate descriptions.
     *
     * @throws IllegalValueException
     */
    private function validateDescriptions(): void
    {
        if (!$this->descriptions) {
            return;
        }

        $this->arrayValidation->length(
            data: $this->descriptions,
            min: 1,
            max: 10000
        );

        foreach ($this->descriptions as $description) {
            $this->stringValidation->length(
                value: $description,
                min: 1,
                max: 10000
            );
        }
    }
}
