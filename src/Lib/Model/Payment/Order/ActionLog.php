<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Order\PaymentActionType;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines an action log item
 */
class ActionLog extends Model
{
    /**
     * @param string $id
     * @param PaymentActionType $type
     * @param string $created
     * @param OrderLineCollection $orderLines
     * @param string|null $transactionId
     * @param string|null $creator
     * @param StringValidation $stringValidation
     * @param ArrayValidation $arrayValidation
     * @throws IllegalValueException
     * @throws IllegalTypeException
     */
    public function __construct(
        public readonly string $id,
        public readonly PaymentActionType $type,
        public readonly string $created,
        public readonly OrderLineCollection $orderLines,
        public readonly ?string $transactionId = null,
        public readonly ?string $creator = null,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly ArrayValidation $arrayValidation = new ArrayValidation(),
    ) {
        $this->validateId();
        $this->validateOrderLines();
        $this->validateCreated();
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateId(): void
    {
        $this->stringValidation->isUuid(value: $this->id);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateCreated(): void
    {
        $this->stringValidation->isDate(value: $this->created);
    }

    /**
     * @return void
     * @throws IllegalValueException
     * @throws IllegalTypeException
     */
    private function validateOrderLines(): void
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
}
