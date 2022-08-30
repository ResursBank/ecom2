<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Validation\FloatValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines a product in an order.
 */
class OrderLine extends Model
{
    /**
     * @param string $description
     * @param string $reference
     * @param OrderLineType $type
     * @param string $quantityUnit
     * @param float $quantity
     * @param float $vatRate
     * @param float $unitAmountIncludingVat
     * @param float $totalAmountIncludingVat
     * @param float $totalVatAmount
     * @param StringValidation $stringValidation
     * @param FloatValidation $floatValidation
     * @throws IllegalValueException
     * @todo $quantity could be a float, or shift between float and int.
     *      We have no idea at the moment.
     */
    public function __construct(
        public readonly string $description,
        public readonly string $reference,
        public readonly OrderLineType $type,
        public readonly string $quantityUnit,
        public readonly float $quantity,
        public readonly float $vatRate,
        public readonly float $unitAmountIncludingVat,
        public readonly float $totalAmountIncludingVat,
        public readonly float $totalVatAmount,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly FloatValidation $floatValidation = new FloatValidation(),
    ) {
        $this->validateDescription();
        $this->validateReference();
        $this->validateQuantityUnit();
        $this->validateVatRate();
        $this->validateQuantity();
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
        $this->floatValidation->inRange(
            value: $this->vatRate,
            min: 0,
            max: 100
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateQuantity(): void
    {
        $whole = floor($this->quantity);
        $fraction = $this->quantity - $whole;

        $this->stringValidation->length(
            value: (string) floor($whole),
            min: 1,
            max: 10
        );

        $this->stringValidation->length(
            value: (string) floor($fraction),
            min: 0,
            max: 5
        );

        $this->floatValidation->inRange(
            value: $this->quantity,
            min: 0,
            max: 9999999999.99
        );
    }
}
