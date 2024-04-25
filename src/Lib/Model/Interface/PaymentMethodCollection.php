<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Interface;

/**
 * Defines payment method collection entity contract.
 */
interface PaymentMethodCollection
{
    /**
     * Find the name of method with specific ID.
     *
     * @param string $methodId
     * @return string
     */
    public function getMethodName(string $methodId): string;

    /**
     * Find method with specific ID.
     *
     * @param string $methodId
     * @return PaymentMethod
     */
    public function getById(string $methodId): PaymentMethod;
}
