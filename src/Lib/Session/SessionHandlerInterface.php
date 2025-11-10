<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Session;

use Resursbank\Ecom\Exception\SessionException;

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
     * @throws SessionException
     */
    public function set(string $key, string $val): void;

    /**
     * @throws SessionException
     */
    public function get(string $key): string;

    public function delete(string $key): void;
}
