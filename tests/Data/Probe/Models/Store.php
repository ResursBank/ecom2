<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models;

use Resursbank\Ecom\Lib\Attribute\Probe\Probable;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Test class.
 */
#[Probable]
class Store extends Model
{
    public function __construct(
        #[StringLength(min: 10, max: 255)] public readonly string $name,
        #[StringLength(min: 0, max: 255)] public readonly ?string $location,
        #[IntValue(min: 0, max: 10000)] public readonly ?int $longitude,
        #[IntValue(min: 0, max: 10000)] public readonly ?int $latitude = null
    ) {
        parent::__construct();
    }
}
