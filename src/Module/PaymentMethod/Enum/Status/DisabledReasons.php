<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Enum\Status;

/**
 * Possible disabled reasons.
 *
 * @codingStandardsIgnoreStart
 */
enum DisabledReasons: string
{
    case AMOUNT_NOT_MATCHING = 'AMOUNT_NOT_MATCHING';
    case NOT_ALLOWED_FOR_CUSTOMER = 'NOT_ALLOWED_FOR_CUSTOMER';
    case DATE_NOT_IN_VALID_RANGE = 'DATE_NOT_IN_VALID_RANGE';
}
