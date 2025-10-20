<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\UserSettingsException;
use Resursbank\Ecom\Module\UserSettings\Repository;

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
     * Checks if supplied log level should be logged according to current configured logLevel.
     *
     * @throws UserSettingsException
     * @todo Check if ConfigException validation needs a test.
     */
    public static function loggable(self $level): bool
    {
        if (!Config::hasInstance()) {
            // If there's no Config instance there's no logLevel restriction to apply.
            return true;
        }

        $settings = Repository::getSettings();

        return $settings->logLevel->value <= $level->value;
    }

    /**
     * This can be used by integrations to render a select field with log levels.
     *
     * @return string[] Associative array of log level values and their names.
     */
    public static function getAssoc(): array
    {
        $result = [];

        foreach (self::cases() as $case) {
            $result[$case->value] = $case->name;
        }

        return $result;
    }
}
