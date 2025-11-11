<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Session;

/**
 * Contract for session handler.
 */
interface SessionHandlerInterface
{
    /**
     * Session key prefix.
     */
    public const PREFIX = 'resursbank_';

    /**
     * Set / update value to session storage.
     */
    public function set(string $key, string $val): void;

    /**
     * Get value from session storage.
     */
    public function get(string $key): string;

    /**
     * Delete value from session storage.
     */
    public function delete(string $key): void;

    /**
     * Check if session is available.
     */
    public function isAvailable(): bool;

    /**
     * Initialize session.
     */
    public function init(): void;

    /**
     * Get the full session key with prefix.
     */
    public static function getKey(string $key): string;
}
