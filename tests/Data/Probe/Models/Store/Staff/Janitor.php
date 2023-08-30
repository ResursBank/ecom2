<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store\Staff;

use Resursbank\EcomTest\Data\Probe\Models\Store\Staff;

/**
 * Test class.
 */
class Janitor extends Staff
{
    public function __construct()
    {
        parent::__construct(name: 'Lars', salary: 661, rank: 'Janitor');
    }
}
