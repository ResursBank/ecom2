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
class CreateTransactionLineCollection extends Collection
{
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: CreateTransactionLine::class);
    }

    /**
     * Extract total sum of intended transaction. Useful for logging.
     */
    public function getTotal(): int
    {
        $result = 0;

        /** @var CreateTransactionLine $line */
        foreach ($this->getData() as $line) {
            if ($line->quantity <= 0) {
                continue;
            }

            $result += $line->unitPrice * $line->quantity;
        }

        return $result;
    }
}
