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
     * @param int $durationMonths
     * @param float $totalCost
     * @param float $monthlyCost
     * @param float $administrationFee
     * @param float $effectiveInterest
     * @param FloatValidation $floatValidation
     * @param IntValidation $intValidation
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly float $interest,
        public readonly int $durationMonths,
        public readonly float $totalCost,
        public readonly float $monthlyCost,
        public readonly float $administrationFee,
        public readonly float $effectiveInterest,
        private readonly FloatValidation $floatValidation = new FloatValidation(),
        private readonly IntValidation $intValidation = new IntValidation()
    ) {
        $this->validateInterest();
        $this->validateDurationMonths();
        $this->validateTotalCost();
        $this->validateMonthlyCost();
        $this->validateAdministrationFee();
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
    private function validateDurationMonths(): void
    {
        $this->intValidation->isPositive(value: $this->durationMonths);
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
    private function validateAdministrationFee(): void
    {
        $this->floatValidation->isPositive(value: $this->administrationFee);
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
