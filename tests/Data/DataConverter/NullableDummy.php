<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\DataConverter;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * To test conversion with missing nullable properties.
 */
class NullableDummy extends Model
{
    public function __construct(
        public int $int,
        public ?string $description
    ) {
    }
}
