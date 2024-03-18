<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

/**
 * Shipping method type for RCO+.
 */
enum Carrier: string
{
    case GENERIC = 'GENERIC';
    case POSTNORD = 'POSTNORD';
}
