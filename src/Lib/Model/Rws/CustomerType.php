<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rws;

/**
 * Defines the types that a customer can be.
 */
enum CustomerType: string
{
    /**
     * Private person.
     */
    case B2C = 'B2C';

    /**
     * Company.
     */
    case B2B = 'B2B';
}
