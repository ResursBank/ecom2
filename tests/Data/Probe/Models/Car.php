<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models;

use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Test class.
 */
class Car extends Model
{
    public function __construct(
        #[StringLength(min: 5, max: 30)] public readonly string $brand,
        #[StringLength(min: 0, max: 54)] public readonly string $model,
        #[IntValue(min: 1000, max: 10000)] public readonly ?int $mileage = null
    ) {
        parent::__construct();
    }
}
