<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Action;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of Management callback data.
 */
class Management extends Model implements CallbackInterface
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringIsUuid] public readonly string $paymentId,
        public readonly Action $action,
        #[StringIsUuid] public readonly string $actionId,
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
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws JsonException
     */
    public function getNote(): string
    {
        return sprintf(
            Translator::translate(phraseId: 'management-callback-received'),
            $this->action->value
        );
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): ?Status
    {
        return null;
    }
}
