<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\GetPayment;

use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Defines a GetPayment spec line collection
 */
class SpecLineCollection extends Collection
{
    /**
     * @param array $data
     * @throws TypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(
            data: $data,
            type: SpecLine::class
        );
    }
}
