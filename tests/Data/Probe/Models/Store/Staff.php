<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store;

use Resursbank\Ecom\Lib\Attribute\Probe\Probable;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Test class.
 */
#[Probable]
class Staff extends Model
{
    public function __construct(
        #[StringLength(min: 10, max: 99)] public readonly string $rank,
        #[IntValue(min: 0, max: 661)] public readonly ?int $salary,
        public readonly string $name,
        #[ArraySize(min: 0, max: 5)] public readonly array $breaks,
        #[ArraySize(min: 5, max: 25)] public readonly array $likes
    ) {
        parent::__construct();
    }
}
