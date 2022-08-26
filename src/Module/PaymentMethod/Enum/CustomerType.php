<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Enum;

/**
 * Possible customer types.
 *
 * @codingStandardsIgnoreStart
 */
enum CustomerType: string
{
    case NATURAL = 'NATURAL';
    case LEGAL = 'LEGAL';
}
