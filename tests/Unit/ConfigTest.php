<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit;

use Exception;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
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
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Log\StdoutLogger;
use Resursbank\Ecom\Lib\Model\Config\Network;
use Resursbank\Ecom\Lib\Model\CurrencyFormat;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\VoidDataHandler;
use Resursbank\Ecom\Lib\Session\Session;
use Resursbank\EcomTest\Utilities\DummySettingsReader;
use Throwable;

/**
 * Tests Config class functionality
 */
#[AllowMockObjectsWithoutExpectations]
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
        self::assertEmpty(actual: Config::getUserAgent());
        self::assertInstanceOf(
            expected: VoidDataHandler::class,
            actual: Config::getPaymentHistoryDataHandler()
        );
        self::assertFalse(condition: Config::isProduction());
        self::assertEquals(
            expected: Language::EN,
            actual: Config::getLanguage()
        );
        self::assertEquals(
            expected: Location::SE,
            actual: Config::getLocation()
        );
        self::assertEquals(
            expected: 'kr',
            actual: Config::getCurrencySymbol()
        );
        self::assertEquals(
            expected: CurrencyFormat::SYMBOL_LAST,
            actual: Config::getCurrencyFormat()
        );
        self::assertEmpty(actual: Config::getProxy());
        self::assertEquals(
            expected: 0,
            actual: Config::getProxyType()
        );
        self::assertEquals(
            expected: 30,
            actual: Config::getTimeout()
        );
        self::assertEmpty(actual: Config::getUserAgent());
        self::assertEquals(
            expected: 0,
            actual: Config::getProxyType()
        );
        self::assertEquals(
            expected: 30,
            actual: Config::getTimeout()
        );
        self::assertNull(
            actual: Config::getStoreId()
        );
        self::assertFalse(
            condition: Config::getCacheWidgets()
        );
        self::assertNull(
            actual: Config::getTemplateOverrideDirectory()
        );
        self::assertInstanceOf(
            expected: Session::class,
            actual: Config::getSessionHandler()
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
        $jwt = new Jwt(
            clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
            clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
            grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
        );

        $network = new Network(userAgent: 'Foo');

        Config::setup(
            logger: new StdoutLogger(),
            cache: new None(),
            jwtAuth: $jwt,
            paymentHistoryDataHandler: new VoidDataHandler(),
            language: Language::SV,
            location: Location::NO,
            currencySymbol: 'dkk',
            currencyFormat: CurrencyFormat::SYMBOL_FIRST,
            network: $network,
            storeId: $_ENV['STORE_ID'],
            cacheWidgets: true,
            settingsReader: new DummySettingsReader(),
            templateOverrideDirectory: '/tmp'
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
        self::assertSame(
            expected: $jwt,
            actual: Config::getJwtAuth()
        );
        self::assertEquals(
            expected: 'Foo',
            actual: Config::getUserAgent()
        );
        self::assertFalse(condition: Config::isProduction());
        self::assertEquals(
            expected: Language::SV,
            actual: Config::getLanguage()
        );
        self::assertEquals(
            expected: 'dkk',
            actual: Config::getCurrencySymbol()
        );
        self::assertEquals(
            expected: CurrencyFormat::SYMBOL_FIRST,
            actual: Config::getCurrencyFormat()
        );
        self::assertEquals(
            expected: $_ENV['STORE_ID'],
            actual: Config::getStoreId()
        );
        self::assertTrue(
            condition: Config::getCacheWidgets()
        );
        self::assertEquals(
            expected: '/tmp',
            actual: Config::getTemplateOverrideDirectory()
        );
    }

    /**
     * Verifies that the hasJwtAuth method behaves as expected
     */
    public function testHasJwtAuth(): void
    {
        Config::setup(
            logger: $this->createMock(type: FileLogger::class)
        );
        self::assertEquals(
            expected: false,
            actual: Config::hasJwtAuth()
        );

        Config::setup(
            logger: $this->createMock(type: FileLogger::class),
            jwtAuth: $this->createMock(type: Jwt::class)
        );
        self::assertEquals(
            expected: true,
            actual: Config::hasJwtAuth()
        );
    }

    /**
     * Verify that hasInstance works as intended.
     */
    public function testHasInstance(): void
    {
        $this->assertFalse(Config::hasInstance());
        Config::setup();
        self::assertTrue(Config::hasInstance());
    }

    /**
     * Verify validateInstance behavior.
     *
     * @throws ConfigException
     */
    public function testValidateInstance(): void
    {
        Config::setup();
        Config::validateInstance();
        self::addToAssertionCount(count: 1);

        Config::unsetInstance();
        self::expectException(exception: ConfigException::class);
        Config::validateInstance();
    }

    /**
     * Verify that exception is thrown for incorrectly formatted path.
     *
     * Assert that a FormatException is thrown and not caught along the way
     * when attempting to run Config::setup with an incorrectly formatted path.
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
            logger: $this->createMock(type: FileLogger::class),
            cache: $this->createMock(type: None::class)
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
