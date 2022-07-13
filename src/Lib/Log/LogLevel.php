<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

/**
 * Defines log levels used by loggers
 */
enum LogLevel
{
    case DEBUG;
    case INFO;
    case WARNING;
    case ERROR;
    case EXCEPTION;
}
