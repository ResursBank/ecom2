<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use JsonException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\PaymentHistory\Translator;

/**
 * Implementation of Credit Application callback data.
 */
class CreditApplication extends Model implements CallbackInterface
{
    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $applicationId,
        public readonly Status $status,
        public readonly string $created,
        public readonly ?string $checkoutId = null,
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
        return $this->applicationId;
    }

    /**
     * Property wrapper to fulfill contract.
     */
    public function getCheckoutId(): ?string
    {
        return $this->checkoutId;
    }

    /**
     * Get note explaining what happened.
     *
     * @throws JsonException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     */
    public function getNote(): string
    {
        return sprintf(
            Translator::translate(
                phraseId: 'credit-application-callback-received'
            ),
            $this->status->value
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validatePaymentId(): void
    {
        $this->stringValidation->notEmpty(value: $this->applicationId);
        $this->stringValidation->isUuid(value: $this->applicationId);
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

    /**
     * @inheritDoc
     */
    public function getStatus(): ?Status
    {
        return $this->status;
    }
}
