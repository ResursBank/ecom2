<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store;

use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Test class.
 */
class Section extends Model
{
    public function __construct(
        #[StringLength(min: 10, max: 99)] public readonly string $name
    ) {
        parent::__construct();
    }
}
