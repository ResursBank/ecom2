<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

/**
 * Methods that is only handled from server side.
 */
class Server
{
    /**
     * Validate and set ip address.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getIp(): ?string
    {
        return self::getValidatedIp(ip: $_SERVER['REMOTE_ADDR'] ?? null);
    }

    /**
     * Validate and return IP address. Used for tests and quick validation of the getIp() request.
     */
    public static function getValidatedIp(?string $ip = null): ?string
    {
        return ($ip !== null && filter_var(
            value: $ip,
            filter: FILTER_VALIDATE_IP
        ))
            ? $ip
            : null;
    }

    /**
     * Get and return a valid User-Agent.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getUserAgent(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (
            is_string(value: $userAgent) &&
            strlen(string: $userAgent) <= 200
        ) {
            return $userAgent;
        }

        return null;
    }
}
