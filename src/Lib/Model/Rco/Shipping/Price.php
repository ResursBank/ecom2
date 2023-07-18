<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\IntValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Price for shippingMethod, used in RCO+.
 */
class Price extends Model
{
    /**
     * @param string $display The display price, this is shown in the checkout.
     * @param int $calculate Price inc tax in minor units create a shipping-cart line if calculateShipping is true.
     * @param int $calculateTax The tax rate used for the calculation as a whole number: 25 for 25%.
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $display,
        public readonly int $calculate,
        public readonly int $calculateTax,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly IntValidation $intValidation = new IntValidation()
    ) {
        $this->validateDisplay();
        $this->validateCalculate();
        $this->validateCalculateTax();
    }

    /**
     * @throws EmptyValueException
     */
    private function validateDisplay(): void
    {
        $this->stringValidation->notEmpty(value: $this->display);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateCalculate(): void
    {
        if ($this->calculate === 0) {
            return;
        }

        $this->intValidation->isPositive(value: $this->calculate);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateCalculateTax(): void
    {
        if ($this->calculateTax === 0) {
            return;
        }

        $this->intValidation->isPositive(value: $this->calculateTax);
    }
}
