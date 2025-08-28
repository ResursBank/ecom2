<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rws;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Defines payment method type map collection.
 */
class PaymentMethodTypeMapCollection extends Collection
{
    /**
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: PaymentMethodTypeMap::class);
    }

    public function getTypeById(string $methodId): ?PaymentMethodType
    {
        /** @var PaymentMethodTypeMap $item */
        foreach ($this->getData() as $item) {
            if ($item->paymentMethodId === $methodId) {
                return $item->type;
            }
        }

        return null;
    }
}
