<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\IntValidation;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\Callbacks;
use Resursbank\Ecom\Module\Payment\Models\CreatePaymentRequest\Options\RedirectionUrls;

/**
 * Application data for a payment.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Options extends Model
{
    /**
     * @param bool|null $initiatedOnCustomerDevice
     * @param bool|null $handleManualInspection
     * @param bool|null $handleFrozenPayments
     * @param bool|null $automaticCapture
     * @param RedirectionUrls|null $redirectionUrls
     * @param Callbacks|null $callbacks
     * @param int|null $timeToLiveInMinutes
     * @param IntValidation $intValidation
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly ?bool $initiatedOnCustomerDevice = null,
        public readonly ?bool $handleManualInspection = null,
        public readonly ?bool $handleFrozenPayments = null,
        public readonly ?bool $automaticCapture = null,
        public readonly ?RedirectionUrls $redirectionUrls = null,
        public readonly ?Callbacks $callbacks = null,
        public readonly ?int $timeToLiveInMinutes = null,
        public readonly IntValidation $intValidation = new IntValidation()
    ) {
        $this->validateTimeToLiveInMinutes();
        $this->validateAutomaticCapture();
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateTimeToLiveInMinutes(): void
    {
        if ($this->timeToLiveInMinutes !== null) {
            $this->intValidation->inRange(value: $this->timeToLiveInMinutes, min: 1, max: 43200);
        }
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateAutomaticCapture(): void
    {
        if ($this->handleFrozenPayments && $this->automaticCapture) {
            throw new IllegalValueException(
                message: 'automaticCapture cannot be set to true when handleFrozenPayments is set to true'
            );
        }
    }
}
