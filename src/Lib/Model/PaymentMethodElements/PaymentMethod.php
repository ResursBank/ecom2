<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethodElements;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Payment Method Elements payment method response.
 */
class PaymentMethod extends Model
{
    /**
     * @param string $id
     * @param array $methodIds
     * @param PaymentMethodType $type
     * @param array $customerTypes
     * @param string $title
     * @param string $subtitle
     * @param string $internalData
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __construct(
        public readonly string $id,
        public readonly array $methodIds,
        public readonly PaymentMethodType $type,
        public readonly array $customerTypes,
        public readonly string $title = '',
        public readonly string $subtitle = '',
        public readonly string $internalData = ''
    ) {
        parent::__construct();
    }
}
