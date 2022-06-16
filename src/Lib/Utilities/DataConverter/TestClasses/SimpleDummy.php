<?php

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
