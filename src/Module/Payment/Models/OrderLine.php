<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Validation\IntValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines a product in an order.
 */
class OrderLine
{
    /**
     * @param string $description
     * @param string $reference
     * @param OrderLineType $type
     * @param string $quantityUnit
     * @param int $quantity
     * @param int $vatRate
     * @param float $unitAmountIncludingVat
     * @param float $totalAmountIncludingVat
     * @param float $totalVatAmount
     * @param StringValidation $stringValidation
     * @param IntValidation $intValidation
     * @throws IllegalValueException
     * @todo $quantity could be a float, or shift between float and int.
     *      We have no idea at the moment.
     */
    public function __construct(
        public readonly string $description,
        public readonly string $reference,
        public readonly OrderLineType $type,
        public readonly string $quantityUnit,
        public readonly int $quantity,
        public readonly int $vatRate,
        public readonly float $unitAmountIncludingVat,
        public readonly float $totalAmountIncludingVat,
        public readonly float $totalVatAmount,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly IntValidation $intValidation = new IntValidation(),
    ) {
        $this->validateDescription();
        $this->validateReference();
        $this->validateQuantityUnit();
        $this->validateVatRate();
    }

    /**
     * @throws IllegalValueException
     * @returns void
     */
    private function validateDescription(): void
    {
        $this->stringValidation->length(
            value: $this->description,
            min: 0,
            max: 50
        );
    }

    /**
     * @throws IllegalValueException
     * @returns void
     */
    private function validateReference(): void
    {
        $this->stringValidation->length(
            value: $this->reference,
            min: 0,
            max: 50
        );
    }

    /**
     * @throws IllegalValueException
     * @returns void
     */
    private function validateQuantityUnit(): void
    {
        $this->stringValidation->length(
            value: $this->quantityUnit,
            min: 0,
            max: 50
        );
    }

    /**
     * @throws IllegalValueException
     * @returns void
     */
    private function validateVatRate(): void
    {
        $this->intValidation->inRange(
            value: $this->vatRate,
            min: 0,
            max: 100
        );
    }
}
