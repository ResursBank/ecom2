<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store\Section;

use Resursbank\Ecom\Lib\Attribute\Probe\Probable;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Test class.
 */
#[Probable]
class Cleaning extends Model
{
    public function __construct(
        #[StringLength(min: 10, max: 500)] public readonly string $name,
        #[StringLength(min: 1, max: 1)] public readonly string $shelf,
        #[IntValue(min: 0, max: 100)] public readonly int $place,
        public readonly array $marks,
        public readonly array $notes
    ) {
        parent::__construct();
    }
}
