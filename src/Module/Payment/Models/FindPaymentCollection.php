<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Module\Payment\Api\FindPayment;

/**
 * Defines a Store collection.
 */
class FindPaymentCollection extends Collection
{
    /**
     * @param array $data
     * @throws TypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(
            data: $data,
            type: FindPayment::class
        );
    }
}
