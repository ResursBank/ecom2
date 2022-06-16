<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities\DataConverter\TestClasses;

use Resursbank\Ecom\Lib\Utilities\DataConverter\TestClasses\SimpleDummy;

class ComplexDummy
{
    public function __construct(
        public int $int,
        public SimpleDummy $simpleDummy
    ) {
    }
}
