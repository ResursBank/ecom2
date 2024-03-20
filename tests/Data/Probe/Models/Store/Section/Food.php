<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store\Section;

use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Test class.
 */
class Food extends Model
{
    public function __construct(
        #[StringLength(min: 5, max: 30)] public readonly string $type,
        public readonly string $brand,
        #[IntValue(min: 0, max: 10000)] public readonly int $price,
        public readonly int $tax
    ) {
        parent::__construct();
    }
}
