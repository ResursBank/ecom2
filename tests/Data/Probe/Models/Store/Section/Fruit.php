<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Probe\Models\Store\Section;

use Resursbank\Ecom\Lib\Attribute\Probe\Probable;

/**
 * Test class.
 */
#[Probable]
class Fruit extends Food
{
    public function __construct()
    {
        parent::__construct(
            type: 'Fruit',
            brand: 'Cool fruit company',
            price: 125,
            tax: 100
        );
    }
}
