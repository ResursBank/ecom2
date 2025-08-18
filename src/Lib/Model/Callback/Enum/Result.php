<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback\Enum;

/**
 * This is used as a return value for callback functions.
 *
 * The purpose of this enum is to allow Repository::process to know how to
 * behave after a callback function has been called.
 */
enum Result: string
{
    case SUCCESS = 'SUCCESS';
    case DELETED = 'DELETED';
}
