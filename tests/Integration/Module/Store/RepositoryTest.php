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
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Store\Repository;
use Throwable;

/**
 * Integration tests for Stores repository.
 */
class RepositoryTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        self::connect(storeId: $_ENV['STORE_ID']);
        parent::setUp();
    }

    /**
     * Establish API connection.
     *
     * @throws EmptyValueException
     */
    private function connect(
        ?string $storeId = null
    ): void {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/stores/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $storeId
        );
    }

    /**
     * Assert correct store data is returned by getConfiguredStore().
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public function testGetConfiguredStore(): void
    {
        // Connect without store id, assert null is returned.
        self::connect();
        $this->assertNull(actual: Repository::getConfiguredStore());

        // Connect with store id from $_ENV, assert store is returned.
        self::connect(storeId: $_ENV['STORE_ID']);
        $this->assertNotNull(actual: Repository::getConfiguredStore());
    }

    /**
     * Assert clearCache() clears cache.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public function testClearCache(): void
    {
        Repository::getStores();
        $this->assertNotNull(actual: Repository::getCache()->read());
        Repository::getCache()->clear();
        $this->assertNull(actual: Repository::getCache()->read());
    }

    /**
     * Assert read() returns data from the API when cache is empty.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public function testReadReturnsWithoutCache(): void
    {
        $this->assertNull(actual: Repository::getCache()->read());
        $this->assertNotEmpty(actual: Repository::getStores());
    }

    /**
     * Verify that cache reads work as intended.
     *
     * Assert read() retrieves stores, store them in cache, and will later
     * return the same stores from cache.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public function testReadReturnsCache(): void
    {
        Repository::getCache()->clear();
        $this->assertNull(actual: Repository::getCache()->read());
        Repository::getStores();
        $this->assertNotNull(actual: Repository::getCache()->read());
    }
}
