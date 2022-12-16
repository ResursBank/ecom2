<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Error;
use Exception;
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

    /**
     * @param string|Throwable $message
     * @return void
     */
    public function debug(string|Throwable $message): void
    {
    }

    /**
     * @param string|Throwable $message
     * @return void
     */
    public function info(string|Throwable $message): void
    {
    }

    /**
     * @param string|Throwable $message
     * @return void
     */
    public function warning(string|Throwable $message): void
    {
    }

    /**
     * @param string|Throwable $message
     * @return void
     */
    public function error(string|Throwable $message): void
    {
    }
}
