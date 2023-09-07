<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of UpdateCheckoutDto.
 */
class UpdateCheckout extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly SetStatus $status,
        public readonly string $selectedPaymentMethodId,
        public readonly Customer $customer,
        public readonly CreateCart $cart,
        public readonly string $orderReference
    ) {
        parent::__construct();
    }
}
