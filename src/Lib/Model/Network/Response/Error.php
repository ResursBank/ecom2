<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network\Response;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Response from some CURL requests contains an error trace.
 */
class Error extends Model
{
    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function __construct(
        #[StringNotEmpty] public readonly string $traceId,
        #[StringNotEmpty] public readonly string $code,
        #[StringNotEmpty] public readonly string $message,
        readonly string $timestamp,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateTimestamp();
        parent::__construct();
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function validateTimestamp(): void
    {
        $this->stringValidation->notEmpty(value: $this->timestamp);
        $this->stringValidation->isTimestampDate(value: $this->timestamp);
    }
}
