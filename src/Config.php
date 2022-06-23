<?php

declare(strict_types=1);

namespace Resursbank\Ecom;

use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Log\LoggerInterface;

/**
 * API communication object.
 */
final class Config
{
    public static Config $instance;

    /**
     * @param Credentials $credentials
     * @param LoggerInterface $logger
     * @param string $logLevel
     * @param string $userAgent
     * @todo Create a null cache driver, so there always is one, returns null always
     * @todo Create a null database driver, so there always is one, returns null always
     */
    public function __construct(
        public readonly Credentials $credentials,
        public readonly LoggerInterface $logger,
        public readonly string $logLevel = 'info',   // Only log info messages.
        public readonly string $userAgent,
        public readonly int $timeout,
    ) {
    }

    /**
     * @param Credentials $credentials
     * @param LoggerInterface $logger
     * @param string $logLevel
     * @param string $userAgent
     * @return void
     */
    public static function setup(
        Credentials $credentials,
        LoggerInterface $logger,
        string $logLevel = 'info',   // Only log info messages.
        string $userAgent = '',
        int $timeout = 0
    ): void {
        self::$instance = new Config(
            $credentials,
            $logger,
            $logLevel,
            $userAgent,
            $timeout
        );

//        self::setupEvents();
//        self::refreshToken();
    }

//    private static function setupEvents(): void
//    {
//        self::$eventHub = new Hub();
        // 1. Load all files from src/Module (only Modules may configure events and listners).
        // 2. Scan all loaded files for Event attributes to setup events in self::eventHub
        // 3. Scan all loaded files for Listner attributes to setup events in self::eventHub. If the Event for the Listner is not defined in the eventHub we should ignore the listner and log this, but not through an Exception since we probably just forget a listner when we removed an event.
//    }
}
