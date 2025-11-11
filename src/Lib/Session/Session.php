<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Session;

use Resursbank\Ecom\Exception\SessionException;
use Resursbank\Ecom\Exception\SessionValueException;

use function is_string;

/**
 * Functionality to store and retrieve data from PHP session.
 *
 * NOTE: Keys in the setter, getter and deleter methods do not automatically
 * call the getKey() method to add the prefix. This is to ensure keys are
 * appropriately prefixed even when the user specifies their own session handler
 * class. Since we have no way of guaranteeing they will use the getKey() method
 * in their implementation. Instead, we need to ensure that everywhere we use
 * these functions within the library, we explicitly call getKey() when passing
 * the key, and the same should of course be done in any calls to these methods
 * from outside the library.
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class Session implements SessionHandlerInterface
{
    /**
     * Session key prefix.
     */
    public const PREFIX = 'resursbank_';

    /**
     * @throws SessionException
     */
    public function set(string $key, string $val): void
    {
        if (!$this->isAvailable()) {
            throw new SessionException(message: 'Session not available.');
        }

        $_SESSION[$key] = $val;
    }

    /**
     * @throws SessionException
     */
    public function get(string $key): string
    {
        if (!$this->isAvailable()) {
            throw new SessionException(message: 'Session not available.');
        }

        if (!isset($_SESSION[$key])) {
            throw new SessionValueException(
                message: "$key not defined in session.",
                code: 404
            );
        }

        if (!is_string(value: $_SESSION[$key])) {
            throw new SessionValueException(
                message: "$key is not a string.",
                code: 415
            );
        }

        return $_SESSION[$key];
    }

    public function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function isAvailable(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function getKey(string $key): string
    {
        return self::PREFIX . $key;
    }
}
