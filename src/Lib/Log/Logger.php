<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Resursbank\Ecom\Config;
use Throwable;

/**
 * Logging wrapper class.
 *
 * This is a wrapper for our logger functions, to allow logging which fails
 * silently without throwing exceptions.
 *
 * This is useful to avoid complicated try-catch blocks in places where
 * logging is not critical to the main flow of execution.
 */
class Logger
{
    /**
     * Attempt to log an error message without throwing exceptions.
     */
    public static function error(Throwable|string $message): void
    {
        try {
            Config::getLogger()->error(message: $message);
        } catch (Throwable) {
            // Do nothing.
        }
    }

    /**
     * Attempt to log a debug message without throwing exceptions.
     */
    public static function debug(Throwable|string $message): void
    {
        try {
            Config::getLogger()->debug(message: $message);
        } catch (Throwable) {
            // Do nothing.
        }
    }

    /**
     * Attempt to log an info message without throwing exceptions.
     */
    public static function info(Throwable|string $message): void
    {
        try {
            Config::getLogger()->info(message: $message);
        } catch (Throwable) {
            // Do nothing.
        }
    }

    /**
     * Attempt to log a warning message without throwing exceptions.
     */
    public static function warning(Throwable|string $message): void
    {
        try {
            Config::getLogger()->warning(message: $message);
        } catch (Throwable) {
            // Do nothing.
        }
    }
}
