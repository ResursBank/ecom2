<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Customer;

/**
 * Enum for product types in RCO+.
 */
enum Type: string
{
    case B2C = 'B2C';
    case B2B = 'B2B';
}
