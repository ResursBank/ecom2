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
        $this->start();

        $_SESSION[self::getKey(key: $key)] = $val;
    }

    /**
     * @throws SessionException
     * @throws SessionValueException
     */
    public function get(string $key): ?string
    {
        $key = self::getKey(key: $key);

        $this->start();

        if (!isset($_SESSION[$key])) {
            return null;
        }

        if (!is_string(value: $_SESSION[$key])) {
            throw new SessionValueException(
                message: "$key is not a string.",
                code: 415
            );
        }

        return $_SESSION[$key];
    }

    /**
     * @throws SessionException
     */
    public function delete(string $key): void
    {
        $this->start();

        unset($_SESSION[self::getKey(key: $key)]);
    }

    public static function getKey(string $key): string
    {
        return self::PREFIX . $key;
    }

    public function isAvailable(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Start session driver, and ensure session is available.
     *
     * @throws SessionException
     */
    public function start(): void
    {
        if (!$this->isAvailable()) {
            $this->init();
        }

        if (!$this->isAvailable()) {
            throw new SessionException(message: 'Session not available.');
        }
    }
}
