<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

class Header
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
        public readonly bool $isStatic = false
    ) {
    }
}
