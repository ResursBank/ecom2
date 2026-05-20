<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;

/**
 * Defines log levels used by loggers.
 */
enum LogLevel: int
{
    case DEBUG = 0;
    case INFO = 1;
    case WARNING = 2;
    case ERROR = 3;
    case EXCEPTION = 4;

    /**
     * Check if configured log level is less than or equal to supplied level.
     *
     * @throws ConfigException
     */
    public static function loggable(self $level): bool
    {
        if (!Config::hasInstance()) {
            // If there's no Config instance there's no logLevel restriction to apply.
            return true;
        }

        return Config::getLogLevel()->value <= $level->value;
    }
}
