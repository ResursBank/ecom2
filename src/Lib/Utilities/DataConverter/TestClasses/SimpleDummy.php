<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities\DataConverter\TestClasses;

class SimpleDummy
{
    public function __construct(
        public int $int,
        public string $message
    ) {
    }
}
