<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Enum\ActionType;

/**
 * Defines an action log item
 */
class ActionLog extends Model
{
    /**
     * @param string $actionId
     * @param ActionType $type
     * @param string $created
     * @param OrderLineCollection $orderLines
     * @param string|null $transactionId
     * @param string|null $creator
     * @param StringValidation $stringValidation
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $actionId,
        public readonly ActionType $type,
        public readonly string $created,
        public readonly OrderLineCollection $orderLines,
        public readonly ?string $transactionId = null,
        public readonly ?string $creator = null,
        public readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateActionId();
        $this->validateCreated();
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateActionId(): void
    {
        $this->stringValidation->isUuid(value: $this->actionId);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateCreated(): void
    {
        $this->stringValidation->isIso8601Date(value: $this->created);
    }
}
