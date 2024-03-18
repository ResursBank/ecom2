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
enum Required: string
{
    case GOVERNMENT_ID = 'GOVERNMENT_ID';
    case EMAIL = 'EMAIL';
    case PHONE = 'PHONE';
    case NAME = 'NAME';
    case ADDRESS = 'ADDRESS';
}
