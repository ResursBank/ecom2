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
    // General settings
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

    // Logging settings
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
    public const XDEBUG_SESSION_VALUE = null;

    // Order management
    /**
     * Enables order management.
     */
    public const ORDER_MANAGEMENT_ENABLE = true;

    // Part payment settings
    /**
     * Default setting for part payment widget is enabled.
     */
    public const PART_PAYMENT_ENABLED = true;

    /**
     * Default limit for part payments in Nordic countries (SE, NO, DK).
     */
    public const PART_PAYMENT_LIMIT = 150;

    /**
     * Default limit for part payments in Euro-based countries (FI).
     */
    public const PART_PAYMENT_LIMIT_EURO = 15;
}
