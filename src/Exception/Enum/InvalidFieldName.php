<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Exception\Enum;

/**
 * Enumeration of invalid field names used in exceptions.
 */
enum InvalidFieldName: string
{
    case GOVERNMENT_ID = 'customer.governmentId';
    case PHONE = 'customer.mobilePhone';
    case EMAIL = 'customer.email';
    case UNKNOWN = '';
}
