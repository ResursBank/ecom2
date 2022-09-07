<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment\Order;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Defines order line (product) collection.
 */
class ActionLogCollection extends Collection
{
    /**
     * @param array<int, ActionLog> $data
     * @param string $type
     * @throws IllegalTypeException
     */
    public function __construct(
        public readonly array $data,
        protected string $type = ActionLog::class
    ) {
        parent::__construct(data: $data, type: $type);
    }
}
