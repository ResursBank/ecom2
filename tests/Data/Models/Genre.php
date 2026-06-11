<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Models;

/**
 * Model which does not inherit from Model.
 */
class Genre
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
