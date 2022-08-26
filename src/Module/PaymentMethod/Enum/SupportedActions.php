<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Enum;

/**
 * Valid payment actions.
 *
 * @codingStandardsIgnoreStart
 */
enum SupportedActions: string
{
    case APPLY_FOR_CREDIT = 'APPLY_FOR_CREDIT';
    case APPLY_FOR_NEW_ACCOUNT = 'APPLY_FOR_NEW_ACCOUNT';
    case LIMIT_RAISE = 'LIMIT_RAISE';
    case AUTHORIZE = 'AUTHORIZE';
    case PART_DEBIT = 'PART_DEBIT';
    case DEBIT = 'DEBIT';
    case PART_CREDIT = 'PART_CREDIT';
    case CREDIT = 'CREDIT';
}
