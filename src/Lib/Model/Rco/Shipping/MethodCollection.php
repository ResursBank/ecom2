<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Method model collection.
 */
class MethodCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: Method::class);
    }
}
