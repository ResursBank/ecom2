<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities\DataConverter\TestClasses;

class ArrayDummy
{
    public function __construct(
        public int $int,
        public array $arr
    ) {
    }
}
