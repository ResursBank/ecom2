<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\FormatException;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Locale;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Log\StdoutLogger;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;

/**
 * Tests Config class functionality
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.SuperGlobals)
 * 
 * @todo Improve test coverage.
 */
class ConfigTest extends TestCase
{
    /**
     * Assert that Config::$instance is properly set up when setup() is called with no parameters
     *
     * @return void
     */
    public function testSetupWithoutParameters(): void
    {
        Config::setup();

        $this->assertInstanceOf(
            expected: NoneLogger::class,
            actual: Config::$instance->logger
        );
        $this->assertInstanceOf(
            expected: None::class,
            actual: Config::$instance->cache
        );
        $this->assertNull(actual: Config::$instance->basicAuth);
        $this->assertNull(actual: Config::$instance->jwtAuth);
        $this->assertEquals(
            expected: LogLevel::INFO,
            actual: Config::$instance->logLevel
        );
        $this->assertEmpty(actual: Config::$instance->userAgent);
        $this->assertFalse(condition: Config::$instance->isProduction);
        $this->assertEmpty(actual: Config::$instance->proxy);
        $this->assertEquals(
            expected: 0,
            actual: Config::$instance->proxyType
        );
        $this->assertEquals(
            expected: 0,
            actual: Config::$instance->timeout
        );
        $this->assertEquals(
            expected: Locale::en,
            actual: Config::$instance->locale
        );
    }

    /**
     * Assert that Config::$instance is properly set up when setup() is called with parameters
     *
     * @return void
     * @throws EmptyValueException
     */
    public function testSetupWithParameters(): void
    {
        Config::setup(
            logger: new StdoutLogger(),
            cache: new None(),
            basicAuth: new Basic(
                username: (string)$_ENV['BASIC_AUTH_USERNAME'],
                password: (string)$_ENV['BASIC_AUTH_PASSWORD']
            ),
            jwtAuth: new Jwt(
                clientId: (string)$_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string)$_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string)$_ENV['JWT_AUTH_SCOPE'],
                grantType: (string)$_ENV['JWT_AUTH_GRANT_TYPE']
            ),
            logLevel: LogLevel::DEBUG,
            userAgent: 'Foo',
            timeout: 42,
            locale: Locale::sv
        );

        $this->assertInstanceOf(
            expected: StdoutLogger::class,
            actual: Config::$instance->logger
        );
        $this->assertInstanceOf(
            expected: None::class,
            actual: Config::$instance->cache
        );
        $this->assertInstanceOf(
            expected: Basic::class,
            actual: Config::$instance->basicAuth
        );
        $this->assertInstanceOf(
            expected: Jwt::class,
            actual: Config::$instance->jwtAuth
        );
        $this->assertEquals(
            expected: LogLevel::DEBUG,
            actual: Config::$instance->logLevel
        );
        $this->assertEquals(
            expected: 'Foo',
            actual: Config::$instance->userAgent
        );
        $this->assertFalse(condition: Config::$instance->isProduction);
        $this->assertEmpty(actual: Config::$instance->proxy);
        $this->assertEquals(
            expected: 0,
            actual: Config::$instance->proxyType
        );
        $this->assertEquals(
            expected: 42,
            actual: Config::$instance->timeout
        );
        $this->assertEquals(
            expected: Locale::sv,
            actual: Config::$instance->locale
        );
    }

    /**
     * Verifies that the hasBasicAuth method behaves as expected
     *
     * @return void
     */
    public function testHasBasicAuth(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $this->assertEquals(
            expected: false,
            actual: Config::hasBasicAuth()
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: $this->createMock(originalClassName: Basic::class)
        );
        $this->assertEquals(
            expected: true,
            actual: Config::hasBasicAuth()
        );
    }

    /**
     * Verifies that the hasJwtAuth method behaves as expected
     *
     * @return void
     */
    public function testHasJwtAuth(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $this->assertEquals(
            expected: false,
            actual: Config::hasJwtAuth()
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: $this->createMock(originalClassName: Jwt::class)
        );
        $this->assertEquals(
            expected: true,
            actual: Config::hasJwtAuth()
        );
    }

    /**
     * Assert that a FormatException is thrown and not caught along the way when attempting to run Config::setup with an
     * incorrectly formatted path
     *
     * @return void
     */
    public function testSetupWithFileLoggerAndTrailingSlash(): void
    {
        $this->expectException(
            exception: FormatException::class
        );

        Config::setup(
            logger: new FileLogger(
                path: '/tmp/'
            )
        );
    }
}
