<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Payment history log entry data model.
 */
class Entry extends Model
{
    /**
     * @param string $paymentId Payment or Checkout ID
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @todo Consider ensuring $extra is JSON encoded. Not sure if this is desirable but seems sensible.
     */
    public function __construct(
        #[StringIsUuid] public readonly string $paymentId,
        public readonly Event $event,
        public readonly User $user,
        public readonly Type $type,
        public readonly ?string $extra,
        public readonly ?string $previousOrderStatus,
        public readonly ?string $currentOrderStatus
    ) {
        parent::__construct();
    }
}
