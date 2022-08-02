<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom;

use ReflectionException;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Lib\Utilities\Generic;

/**
 * API communication object.
 */
final class Config
{
    public static Config $instance;

    /**
     * @param LoggerInterface $logger
     * @param Basic|null $basicAuth
     * @param Jwt|null $jwtAuth
     * @param LogLevel $logLevel
     * @param string $userAgent
     * @param bool $isProduction
     * @param string $proxy
     * @param int $proxyType
     * @param int $timeout
     * @todo Create a null cache driver, so there always is one, returns null always
     * @todo Create a null database driver, so there always is one, returns null always
     */
    public function __construct(
        public readonly LoggerInterface $logger,
        public readonly Basic|null $basicAuth,
        public readonly Jwt|null $jwtAuth,
        public readonly LogLevel $logLevel = LogLevel::INFO,   // Only log info messages.
        public readonly ?string $userAgent = '',
        public readonly bool $isProduction = false,
        public readonly string $proxy = '',
        public readonly int $proxyType = 0,
        public readonly int $timeout = 60,
    ) {
    }

    /**
     * @param LoggerInterface $logger
     * @param Basic|null $basicAuth
     * @param Jwt|null $jwtAuth
     * @param LogLevel $logLevel
     * @param string|null $userAgent
     * @param bool $isProduction
     * @param string $proxy
     * @param int $proxyType
     * @param int $timeout
     * @return void
     */
    public static function setup(
        LoggerInterface $logger,
        Basic|null $basicAuth = null,
        Jwt|null $jwtAuth = null,
        LogLevel $logLevel = LogLevel::INFO,   // Only log info messages.
        ?string $userAgent = '',
        bool $isProduction = false,
        string $proxy = '',
        int $proxyType = 0,
        int $timeout = 0
    ): void {
        self::$instance = new Config(
            logger: $logger,
            basicAuth: $basicAuth,
            jwtAuth: $jwtAuth,
            logLevel: $logLevel,
            userAgent: self::setupUserAgent($userAgent),
            isProduction: $isProduction,
            proxy: $proxy,
            proxyType: $proxyType,
            timeout: $timeout
        );

//        self::setupEvents();
//        self::refreshToken();
    }

    /**
     * Prepare user agent data.
     *
     * @param string|null $userAgent
     * @return string
     * @throws ReflectionException
     */
    private static function setupUserAgent(?string $userAgent = ''): string
    {
        if (class_exists($userAgent)) {
            // If user agent string is a class, we try to extract proper data automatically from the class short
            // name and docblock version.
            $genericAgent = new Generic();
            $classVersion = $genericAgent->getVersionByClassDoc($userAgent);
            $userAgentClass = explode('\\', $userAgent);
            $userAgentShortName = $userAgentClass[count($userAgentClass) - 1];

            // Version number will only be added to the class name if it can be found in the docblock.
            $userAgent = sprintf(
                '%s%s',
                $userAgentShortName,
                !empty($classVersion) ? '-' . $classVersion : ''
            );
        }

        return $userAgent;
    }

    /*private static function setupEvents(): void
    {
        self::$eventHub = new Hub();
        // 1. Load all files from src/Module (only Modules may configure events and listeners).
        // 2. Scan all loaded files for Event attributes to set up events in self::eventHub
        // 3. Scan all loaded files for Listener attributes to set up events in self::eventHub. If the Event for the
        // Listener is not defined in the eventHub we should ignore the listener and log this, but not through an
        // Exception since we probably just forget a listener when we removed an event.
    }*/
}
