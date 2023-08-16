<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCart;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\IntValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of CrateCartItemDto object.
 */
class Item extends Model
{
    /**
     * @param array|null $tags
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly CartItemType $type,
        public readonly string $itemId,
        public readonly string $description,
        public readonly string $quantityUnit,
        public readonly int $unitPrice,
        public readonly ?int $quantity,
        public readonly ?int $taxRate = null,
        public readonly ?int $totalDiscount = null,
        public readonly ?string $url = null,
        public readonly ?string $imageUrl = null,
        public readonly ?array $tags = null,
        public readonly ?bool $mutable = null,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly IntValidation $intValidation = new IntValidation(),
        private readonly ArrayValidation $arrayValidation = new ArrayValidation()
    ) {
        $this->validateItemId();
        $this->validateDescription();
        $this->validateQuantityUnit();
        $this->validateQuantity();
        $this->validateTaxRate();
        $this->validateTotalDiscount();
        $this->validateUrl();
        $this->validateImageUrl();
        $this->validateTags();
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validateItemId(): void
    {
        $this->stringValidation->notEmpty(value: $this->itemId);
        $this->stringValidation->length(value: $this->itemId, min: 1, max: 36);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateDescription(): void
    {
        if ($this->description === '') {
            return;
        }

        $this->stringValidation->length(
            value: $this->description,
            min: 1,
            max: 280
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validateQuantityUnit(): void
    {
        $this->stringValidation->notEmpty(value: $this->quantityUnit);
        $this->stringValidation->length(
            value: $this->quantityUnit,
            min: 1,
            max: 32
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateQuantity(): void
    {
        if ($this->quantity === null) {
            return;
        }

        $this->intValidation->isPositive(value: $this->quantity);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateTaxRate(): void
    {
        if ($this->taxRate === null) {
            return;
        }

        $this->intValidation->inRange(value: $this->taxRate, min: 0, max: 100);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateTotalDiscount(): void
    {
        if ($this->totalDiscount === null) {
            return;
        }

        $this->intValidation->isPositive(value: $this->totalDiscount);
    }

    /**
     * @throws IllegalCharsetException
     */
    private function validateUrl(): void
    {
        if ($this->url === null) {
            return;
        }

        $this->stringValidation->matchRegex(
            value: $this->url,
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/'
        );
    }

    /**
     * @throws IllegalCharsetException
     */
    private function validateImageUrl(): void
    {
        if ($this->imageUrl === null) {
            return;
        }

        $this->stringValidation->matchRegex(
            value: $this->imageUrl,
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/'
        );
    }

    /**
     * @throws IllegalValueException
     */
    private function validateTags(): void
    {
        if ($this->tags === null) {
            return;
        }

        $this->arrayValidation->length(data: $this->tags, min: 0, max: 10);
    }
}
