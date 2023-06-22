<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\FloatValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Order row items in RCO+.
 */
class Item extends Model
{
    /**
     * @param Type $type Describes the type of line.
     * @param string $itemId SKU or other unique id.
     * @param string $description Short description or name of the product or service.
     * @param string $quantityUnit Type of quantityUnit, examples: st, l, kg etc.
     * @param float $quantity The quantity.
     * @param float $unitPrice The price per quantityUnit including tax expressed in minor units.
     * @param int $taxRate Tax rate. 25 for 25% or 0 for 0%.
     * @param float $totalDiscount Only used to display totalDiscount for this cart line in the cart.
     * @param string $url A url to a description of the product or service.
     * @param string $imageUrl A url to an image of the product or service.
     * @param array $tags A list of optional string tags.
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly Type $type,
        public readonly string $itemId,
        public readonly string $description,
        public readonly string $quantityUnit,
        public readonly float $quantity,
        public readonly float $unitPrice,
        public readonly int $taxRate,
        public readonly float $totalDiscount,
        public readonly string $url,
        public readonly string $imageUrl,
        public readonly array $tags = [],
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly FloatValidation $floatValidation = new FloatValidation()
    ) {
        $this->validateDescription();
        $this->validateItemId();
        $this->validateQuantityUnit();
        $this->validateQuantity();
        $this->validateUnitPrice();
    }

    /**
     * Validate that description has content.
     *
     * @throws EmptyValueException
     */
    private function validateDescription(): void
    {
        $this->stringValidation->notEmpty(value: $this->description);
    }

    /**
     * Validate that the SKU or unique id has content.
     *
     * @throws EmptyValueException
     */
    private function validateItemId(): void
    {
        $this->stringValidation->notEmpty(value: $this->itemId);
    }

    /**
     * Validate that there is a quantity unit present.
     *
     * @throws EmptyValueException
     */
    private function validateQuantityUnit(): void
    {
        $this->stringValidation->notEmpty(value: $this->quantityUnit);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateQuantity(): void {
        $this->floatValidation->isPositive(value: $this->quantity);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateUnitPrice(): void
    {
        if (!is_float(value: $this->unitPrice)) {
            return;
        }

        $this->floatValidation->length(
            value: $this->unitPrice,
            min: 0,
            max: 2
        );

        $this->floatValidation->inRange(
            value: $this->unitPrice,
            min: -9999999999.99,
            max: 9999999999.99
        );
    }
}
