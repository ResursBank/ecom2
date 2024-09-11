<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Converter;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\FloatValue;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\FloatValidation;

/**
 * Object containing amount of discount applied with specific VAT rate.
 */
class DiscountItem extends Model
{
    /**
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[FloatValue(min: 0.0, max: 99.99)] public readonly float $rate,
        #[FloatValue(min: 0.0, max: 9999999999.99)] public float $amount = 0.0,
        private readonly FloatValidation $floatValidation = new FloatValidation()
    ) {
        $this->validateRate();

        parent::__construct();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateRate(): void
    {
        $this->floatValidation->length(value: $this->rate, min: 0, max: 2);
    }
}
