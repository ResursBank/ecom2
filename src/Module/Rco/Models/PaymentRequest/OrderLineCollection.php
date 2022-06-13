<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\PaymentRequest;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Defines OrderLine collections
 */
class OrderLineCollection extends Collection
{
    protected ?string $type = OrderLine::class;
}
