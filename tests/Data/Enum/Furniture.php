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
enum Furniture: string
{
    case CHAIR = 'CHAIR';
    case TABLE = 'TABLE';
    case LAMP = 'LAMP';
    case SOFA = 'SOFA';
    case SHELF = 'SHELF';
    case BOOKCASE = 'BOOKCASE';
}
