<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Enum;

/**
 * Valid customer type values.
 *
 * @codingStandardsIgnoreStart
 */
enum CustomerType: string
{
    case NATURAL = 'NATURAL';
    case LEGAL = 'LEGAL';
}
