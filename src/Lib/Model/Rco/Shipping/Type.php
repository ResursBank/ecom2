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
enum Type: string
{
    case PICKUP = 'PICKUP';
    case IN_STORE = 'IN_STORE';
    case MAILBOX = 'MAILBOX';
    case DELIVERY = 'DELIVERY';
}
