<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Store;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Store\Repository;

/**
 * Integration tests for Stores repository.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws EmptyValueException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: new Filesystem(path: '/tmp/ecom-test/stores/' . time()),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        parent::setUp();
    }

    /**
     * Assert clearCache() clears cache.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws IllegalTypeException
     */
    public function testClearCache(): void
    {
        Repository::getStores();
        self::assertNotNull(actual: Repository::getCache()->read());
        Repository::getCache()->clear();
        self::assertNull(actual: Repository::getCache()->read());
    }

    /**
     * Assert read() returns data from the API when cache is empty.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testReadReturnsWithoutCache(): void
    {
        self::assertNull(actual: Repository::getCache()->read());
        self::assertNotEmpty(actual: Repository::getStores());
    }

    /**
     * Assert read() retrieves stores, store them in cache, and will later
     * return the same stores from cache.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testReadReturnsCache(): void
    {
        Repository::getCache()->clear();
        self::assertNull(actual: Repository::getCache()->read());
        Repository::getStores();
        self::assertNotNull(actual: Repository::getCache()->read());
    }
}
