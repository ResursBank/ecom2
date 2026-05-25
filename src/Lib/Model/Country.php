<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

/**
 * Country codes supported by Store entity.
 *
 * @codingStandardsIgnoreStart
 */
enum Country: string
{
    case SE = 'SE';
    case NO = 'NO';
    case FI = 'FI';
    case DK = 'DK';
    case UNKNOWN = 'UNKNOWN';
}
