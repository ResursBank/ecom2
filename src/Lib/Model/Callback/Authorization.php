<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of Authorization callback data.
 */
class Authorization extends Model implements CallbackInterface
{
    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly Status $status,
        public readonly string $created,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validatePaymentId();
        $this->validateCreated();
    }

    /**
     * Property wrapper to fulfill contract.
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function getNote(): string
    {
        return "Authorization callback received. Status: {$this->status->value}";
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validatePaymentId(): void
    {
        $this->stringValidation->notEmpty(value: $this->paymentId);
        $this->stringValidation->isUuid(value: $this->paymentId);
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validateCreated(): void
    {
        $this->stringValidation->notEmpty(value: $this->created);
        $this->stringValidation->isTimestampDate(value: $this->created);
    }
}
