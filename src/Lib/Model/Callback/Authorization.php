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
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\PaymentHistory\Translator;

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
        #[StringIsUuid] public readonly string $paymentId,
        public readonly Status $status,
        #[StringIsDatetime] public readonly string $created,
        public readonly ?string $checkoutId = null
    ) {
        parent::__construct();
    }

    /**
     * Property wrapper to fulfill contract.
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
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
            Translator::translate(phraseId: 'authorization-callback-received'),
            $this->status->value
        );
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): ?Status
    {
        return $this->status;
    }
}
