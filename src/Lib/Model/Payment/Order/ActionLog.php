<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Enum\ActionType;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;

/**
 * Defines an action log item.
 */
class ActionLog extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringIsUuid] public readonly string $actionId,
        public readonly ActionType $type,
        #[StringIsDatetime] public readonly string $created,
        #[CollectionSize(
            min: 1,
            max: 100
        )] public readonly OrderLineCollection $orderLines,
        public readonly ?string $transactionId = null,
        public readonly ?string $creator = null
    ) {
        parent::__construct();
    }
}
