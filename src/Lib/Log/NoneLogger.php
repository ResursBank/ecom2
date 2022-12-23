<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Throwable;

/**
 * Logs nothing.
 */
class NoneLogger implements LoggerInterface
{
    /**
     * Doesn't initialize anything
     */
    public function __construct()
    {
    }

    public function debug(string|Throwable $message): void
    {
    }

    public function info(string|Throwable $message): void
    {
    }

    public function warning(string|Throwable $message): void
    {
    }

    public function error(string|Throwable $message): void
    {
    }
}
