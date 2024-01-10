<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Enum;

/**
 * Possible checkout session statuses.
 */
enum CheckoutStatus: string
{
    case CREATED = 'CREATED';
    case INITIATED = 'INITIATED';
    case VALIDATED = 'VALIDATED';
    case PAID = 'PAID';
    case DELIVERED = 'DELIVERED';
    case VERIFYING = 'VERIFYING';
}
