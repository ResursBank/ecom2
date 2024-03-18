<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Enum;

/**
 * Possible payment statuses.
 */
enum PaymentStatus: string
{
    case NONE = 'NONE';
    case CREATED = 'CREATED';
    case FAILED = 'FAILED';
    case AUTHORIZED = 'AUTHORIZED';
    case CANCELLED = 'CANCELLED';
    case CAPTURED = 'CAPTURED';
    case REFUNDED = 'REFUNDED';
}
