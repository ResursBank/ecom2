<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rws;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethod\MethodOptionCollection;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethod\Properties;

/**
 * RWS payment method response.
 */
class PaymentMethod extends Model
{
    /**
     * @param PaymentMethodType $type
     * @param array $methodIds
     * @param array $customerTypes
     * @param Properties $properties
     * @param MethodOptionCollection $methodOptions
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly PaymentMethodType $type,
        public readonly array $methodIds,
        public readonly array $customerTypes,
        public readonly Properties $properties,
        public readonly MethodOptionCollection $methodOptions
    ) {
        parent::__construct();
    }
}