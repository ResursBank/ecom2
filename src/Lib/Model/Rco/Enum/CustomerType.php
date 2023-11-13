<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Enum;

/**
 * Enum for required fields.
 */
enum CustomerType: string
{
    case B2B = 'B2B';
    case B2C = 'B2C';
}
