<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Enum;

/**
 * Enum of payment selection type.
 */
enum PaymentSelection: string
{
    case DEFAULT = 'DEFAULT';
    case FALLBACK = 'FALLBACK';
    case USER_CHOICE = 'USER_CHOICE';
}
