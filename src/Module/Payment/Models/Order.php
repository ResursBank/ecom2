<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines an order.
 */
class Order extends Model
{
    /**
     * @param OrderLineCollection $orderLines
     * @param string $orderReference
     * @param StringValidation $stringValidation
     * @param ArrayValidation $arrayValidation
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     * @throws IllegalTypeException
     */
    public function __construct(
        public readonly OrderLineCollection $orderLines,
        public readonly string $orderReference,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly ArrayValidation $arrayValidation = new ArrayValidation(),
    ) {
        $this->validateOrderLines();
        $this->validateOrderReference();
    }

    /**
     * @throws IllegalValueException
     * @throws IllegalTypeException
     */
    public function validateOrderLines(): void
    {
        $this->arrayValidation->isSequential(data: $this->orderLines->data);
        $this->arrayValidation->length(
            data: $this->orderLines->data,
            min: 1,
            max: 1000
        );
        $this->arrayValidation->isOfType(
            data: $this->orderLines->data,
            type: OrderLine::class,
            compareFn: fn (mixed $value) => $value instanceof OrderLine
        );
    }

    /**
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function validateOrderReference(): void
    {
        $this->stringValidation->length(
            value: $this->orderReference,
            min: 1,
            max: 32
        );

        $this->stringValidation->matchRegex(
            value: $this->orderReference,
            pattern: '/[\w\-_]+/'
        );
    }
}
