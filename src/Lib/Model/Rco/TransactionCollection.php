<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Transaction collection
 */
class TransactionCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: Transaction::class);
    }
}
