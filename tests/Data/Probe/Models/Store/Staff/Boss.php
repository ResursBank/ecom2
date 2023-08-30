<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store\Staff;

use Resursbank\Ecom\Lib\Attribute\Probe\Probable;
use Resursbank\EcomTest\Data\Probe\Models\Store\Staff;

/**
 * Test class.
 */
#[Probable]
class Boss extends Staff
{
    public function __construct()
    {
        parent::__construct(
            name: 'Kalle',
            salary: 500,
            rank: 'the boss',
            breaks: [],
            likes: ['Jan', 'Sean', 'Sarah', 'Clara', 'Peanut', 'Baking']
        );
    }
}
