<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\FormatException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Log\StdoutLogger;
use Resursbank\Ecom\Lib\Model\Config\Network;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Throwable;

/**
 * Tests Config class functionality
 *
 * @todo Improve test coverage.
 */
class ConfigTest extends TestCase
{
    /**
     * Assert that Config::$instance is properly set up when setup() is called with no parameters
     *
     * @throws ConfigException
     */
    public function testSetupWithoutParameters(): void
    {
        Config::setup();

        self::assertInstanceOf(
            expected: NoneLogger::class,
            actual: Config::getLogger()
        );
        self::assertInstanceOf(
            expected: None::class,
            actual: Config::getCache()
        );
        self::assertNull(actual: Config::getJwtAuth());
        self::assertEquals(
            expected: LogLevel::INFO,
            actual: Config::getLogLevel()
        );
        self::assertEmpty(actual: Config::getUserAgent());
        self::assertFalse(condition: Config::isProduction());
        self::assertEmpty(actual: Config::getProxy());
        self::assertEquals(
            expected: 0,
            actual: Config::getProxyType()
        );
        self::assertEquals(
            expected: 0,
            actual: Config::getTimeout()
        );
        self::assertEquals(
            expected: Language::EN,
            actual: Config::getLanguage()
        );
        self::assertEquals(
            expected: Location::SE,
            actual: Config::getLocation()
        );
    }

    /**
     * Unset config before each test runs.
     */
    public function setUp(): void
    {
        Config::unsetInstance();
    }

    /**
     * Assert that Config::$instance is properly set up when setup() is called with parameters
     *
     * @throws EmptyValueException
     * @throws ConfigException
     */
    public function testSetupWithParameters(): void
    {
        Config::setup(
            logger: new StdoutLogger(),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            logLevel: LogLevel::DEBUG,
            language: Language::SV,
            network: new Network(
                timeout: 42,
                userAgent: 'Foo'
            ),
            storeId: $_ENV['STORE_ID']
        );

        self::assertInstanceOf(
            expected: StdoutLogger::class,
            actual: Config::getLogger()
        );
        self::assertInstanceOf(
            expected: None::class,
            actual: Config::getCache()
        );
        self::assertInstanceOf(
            expected: Jwt::class,
            actual: Config::getJwtAuth()
        );
        self::assertEquals(
            expected: LogLevel::DEBUG,
            actual: Config::getLogLevel()
        );
        self::assertEquals(
            expected: 'Foo',
            actual: Config::getUserAgent()
        );
        self::assertFalse(condition: Config::isProduction());
        self::assertEmpty(actual: Config::getProxy());
        self::assertEquals(
            expected: 0,
            actual: Config::getProxyType()
        );
        self::assertEquals(
            expected: 42,
            actual: Config::getTimeout()
        );
        self::assertEquals(
            expected: Language::SV,
            actual: Config::getLanguage()
        );
        self::assertEquals(
            expected: $_ENV['STORE_ID'],
            actual: Config::getStoreId()
        );
    }

    /**
     * Verifies that the hasJwtAuth method behaves as expected
     */
    public function testHasJwtAuth(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        self::assertEquals(
            expected: false,
            actual: Config::hasJwtAuth()
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: $this->createMock(originalClassName: Jwt::class)
        );
        self::assertEquals(
            expected: true,
            actual: Config::hasJwtAuth()
        );
    }

    /**
     * Assert that a FormatException is thrown and not caught along the way when attempting to run Config::setup with an
     * incorrectly formatted path
     */
    public function testSetupWithFileLoggerAndTrailingSlash(): void
    {
        $this->expectException(exception: FormatException::class);

        Config::setup(
            logger: new FileLogger(
                path: '/tmp/'
            )
        );
    }

    /**
     * Verifies that the $instance property is set to null before setup.
     */
    public function testInstanceNullBeforeSetup(): void
    {
        $reflectionClass = new ReflectionClass(objectOrClass: Config::class);
        $uninitializedValue = $reflectionClass->getStaticPropertyValue(
            name: 'instance'
        );

        $this->assertNull(actual: $uninitializedValue);

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            cache: $this->createMock(originalClassName: None::class)
        );

        $initializedValue = $reflectionClass->getStaticPropertyValue(
            name: 'instance'
        );

        $this->assertNotNull(actual: $initializedValue);
    }

    /**
     * Test valid path without traversal
     *
     * @throws Exception
     */
    public function testGetPathWithValidDirectory(): void
    {
        $path = Config::getPath(dir: 'valid/directory');
        $expectedPath = dirname(path: __DIR__, levels: 2) . '/valid/directory';
        $this->assertEquals(expected: $expectedPath, actual: $path);
    }

    /**
     * Test empty path (should return ECom root)
     *
     * @throws Exception
     */
    public function testGetPathWithEmptyDirectory(): void
    {
        $path = Config::getPath(dir: '');
        $expectedPath = dirname(path: __DIR__, levels: 2);
        $this->assertEquals(expected: $expectedPath, actual: $path);
    }

    /**
     * Test directory traversal attack prevention
     *
     * @throws Exception
     */
    public function testGetPathWithDirectoryTraversal(): void
    {
        $this->expectException(exception: Throwable::class);
        $this->expectExceptionMessage(
            message: 'Invalid directory path. Directory traversal is not allowed.'
        );

        Config::getPath(dir: '../etc/passwd');
    }

    /**
     * Test valid path with leading slash
     *
     * @throws Exception
     */
    public function testGetPathWithLeadingSlash(): void
    {
        $path = Config::getPath(dir: '/subdir/with/leading/slash');
        $expectedPath = dirname(
            path: __DIR__,
            levels: 2
        ) . '/subdir/with/leading/slash';

        $this->assertEquals(expected: $expectedPath, actual: $path);
    }

    /**
     * Test path that only contains ".." (should trigger traversal prevention)
     *
     * @throws Exception
     */
    public function testGetPathWithOnlyTraversal(): void
    {
        $this->expectException(exception: Throwable::class);
        $this->expectExceptionMessage(
            message: 'Invalid directory path. Directory traversal is not allowed.'
        );

        Config::getPath(dir: '..');
    }

    /**
     * Test path containing multiple ".." segments (should trigger traversal prevention)
     *
     * @throws Exception
     */
    public function testGetPathWithMultipleTraversalSegments(): void
    {
        $this->expectException(exception: Throwable::class);
        $this->expectExceptionMessage(
            message: 'Invalid directory path. Directory traversal is not allowed.'
        );

        Config::getPath(dir: 'some/../../directory');
    }
}
