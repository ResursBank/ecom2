<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PriceSignage\Models;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\FloatValidation;
use Resursbank\Ecom\Lib\Validation\IntValidation;

/**
 * Defines cost entity.
 */
class Cost extends Model
{
    /**
     * @param float $interest
     * @param int $months
     * @param float $totalCost
     * @param float $monthlyCost
     * @param float $agreementFee
     * @param float $effectiveInterest
     * @param FloatValidation $floatValidation
     * @param IntValidation $intValidation
     * @throws IllegalValueException
     * @todo Ask about validation rules for this. Can the floats be negative? empty? min / max val? decimals always 2?
     * @todo Months is specified as int32, meaning I could get 2,147,483,647 back? Is that correct? ~179 million years.
     */
    public function __construct(
        public readonly float $interest,
        public readonly int $months,
        public readonly float $totalCost,
        public readonly float $monthlyCost,
        public readonly float $agreementFee,
        public readonly float $effectiveInterest,
        private readonly FloatValidation $floatValidation = new FloatValidation(),
        private readonly IntValidation $intValidation = new IntValidation()
    ) {
        $this->validateInterest();
        $this->validateMonths();
        $this->validateTotalCost();
        $this->validateMonthlyCost();
        $this->validateAgreementFee();
        $this->validateEffectiveInterest();
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateInterest(): void
    {
        $this->floatValidation->isPositive(value: $this->interest);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateMonths(): void
    {
        $this->intValidation->isPositive(value: $this->months);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateTotalCost(): void
    {
        $this->floatValidation->isPositive(value: $this->totalCost);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateMonthlyCost(): void
    {
        $this->floatValidation->isPositive(value: $this->monthlyCost);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateAgreementFee(): void
    {
        $this->floatValidation->isPositive(value: $this->agreementFee);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateEffectiveInterest(): void
    {
        $this->floatValidation->isPositive(value: $this->effectiveInterest);
    }
}
