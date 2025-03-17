<?php

/** @noinspection PhpUnused */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Config\Settings;

use Resursbank\Ecom\Lib\Log\LogLevel;

/**
 * Defaults for configuration options in plugins.
 */
class Defaults
{
    /**
     * Default timeout value (in seconds) for API requests.
     */
    public const API_TIMEOUT = 30;

    /**
     * Determines whether caching is enabled by default.
     */
    public const CACHE_ENABLED = true;

    /**
     * Enables address retrieval functionality.
     */
    public const GET_ADDRESS_ENABLED = true;

    /**
     * Specifies whether logging is enabled.
     */
    public const LOG_ENABLED = true;

    /**
     * Default log level, retrieved from LogLevel.
     */
    public const LOG_LEVEL = LogLevel::INFO;

    /**
     * Default value for Xdebug session.
     */
    public const XDEBUG_SESSION_VALUE = '';

    /**
     * Enables order cancellation in order management.
     */
    public const ORDER_MANAGEMENT_ENABLE_CANCEL = true;

    /**
     * Enables order capture functionality in order management.
     */
    public const ORDER_MANAGEMENT_ENABLE_CAPTURE = true;

    /**
     * Enables modification of existing orders in order management.
     */
    public const ORDER_MANAGEMENT_ENABLE_MODIFY = true;

    /**
     * Enables order refunds in order management.
     */
    public const ORDER_MANAGEMENT_ENABLE_REFUND = true;

    /**
     * Default limit for part payments in Nordic countries (SE, NO, DK) vs Finland (FI uses EURO).
     */
    public const PART_PAYMENT_LIMIT_NORDIC = 150;
    public const PART_PAYMENT_LIMIT_EURO = 15;

    /**
     * Retrieve an option value.
     */
    public static function get(string $key): mixed
    {
        return defined(constant_name: "self::$key")
            ? constant(name: "self::$key")
            : null;
    }
}
