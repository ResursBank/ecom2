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
        public readonly ?SetStatus $status = null,
        public readonly ?string $selectedPaymentMethodId = null,
        public readonly ?UpdateCustomer $customer = null,
        public readonly ?CreateCart $cart = null,
        public readonly ?string $orderReference = null
    ) {
        parent::__construct();
    }
}
