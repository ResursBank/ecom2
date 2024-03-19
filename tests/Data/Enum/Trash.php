<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\Enum;

/**
 * Enum for testing.
 */
enum Trash: string
{
    case FOOD = 'FOOD';
    case PAPER = 'PAPER';
    case PLASTIC = 'PLASTIC';
}
