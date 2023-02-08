<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of Authorization callback data.
 */
class Authorization extends Model
{
    /**
     * @param StringValidation $stringValidation
     * @throws EmptyValueException
     * @todo Incomplete property validation.
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
     * @throws EmptyValueException
     */
    private function validatePaymentId(): void
    {
        $this->stringValidation->notEmpty(value: $this->paymentId);
    }

    /**
     * @throws EmptyValueException
     * // @todo Could validate it's a date.
     */
    private function validateCreated(): void
    {
        $this->stringValidation->notEmpty(value: $this->created);
    }
}
