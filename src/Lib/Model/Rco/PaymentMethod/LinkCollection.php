<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Link collection.
 */
class LinkCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: Link::class);
    }
}
