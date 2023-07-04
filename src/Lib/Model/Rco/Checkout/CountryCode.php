<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

/**
 * Enum for country codes in RCO+, in ISO 3166-1 Alpha-2.
 */
enum CountryCode: string
{
    case SE = 'SE';
    case DK = 'DK';
    case NO = 'NO';
    case FI = 'FI';
}
