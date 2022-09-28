<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod;

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
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Lib\Repository\Cache;

/**
 * Integration tests for PaymentMethods repository.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @var Cache
     */
    private Cache $cache;

    /**
     * @var string
     */
    private string $storeId;

    /**
     * @return void
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        $this->storeId = (string) $_ENV['STORE_ID'];

        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: new Filesystem(path: '/tmp/ecom-test/paymentMethods/' . time()),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        $this->cache = Repository::getCache(storeId: $this->storeId);
        $this->cache->clear();

        parent::setUp();
    }

    /**
     * Assert clearCache() clears cache.
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
    public function testClearCache(): void
    {
        Repository::getPaymentMethods(storeId: $this->storeId);

        self::assertNotNull(actual: $this->cache->read());

        $this->cache->clear();

        self::assertNull(actual: $this->cache->read());
    }

    /**
     * Assert read() returns data from the API when cache is empty.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws EmptyValueException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testReadReturnsWithoutCache(): void
    {
        self::assertNull(actual: $this->cache->read());
        self::assertNotEmpty(
            actual: Repository::getPaymentMethods(
                storeId: $this->storeId
            )
        );
    }

    /**
     * Assert read() retrieves payment methods, paymentMethod them in cache, and
     * will later return the same paymentMethods from cache.
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
        self::assertEmpty(actual: $this->cache->read());

        $data = Repository::getPaymentMethods(
            storeId: $this->storeId
        );

        self::assertNotEmpty(actual: $data);

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        self::assertEquals(expected: $data, actual: $this->cache->read());
    }

    /**
     * Assert different datasets from the API for different amount values. Also
     * make sure the cache is kept separated by the same value.
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
    public function testDataSeparatedByAmount(): void
    {
        $storeId = $this->storeId;

        $amount1 = 11;
        $amount2 = 1000;

        // Load data from API to cache.
        $apiData1 = Repository::getPaymentMethods(
            storeId: $storeId,
            amount: $amount1
        );

        $apiData2 = Repository::getPaymentMethods(
            storeId: $storeId,
            amount: $amount2
        );

        // Retrieve same data from cache.
        $cacheData1 = Repository::getCache(
            storeId: $storeId,
            amount: $amount1
        )->read();

        $cacheData2 = Repository::getCache(
            storeId: $storeId,
            amount: $amount2
        )->read();

        self::assertEquals(expected: $apiData1, actual: $cacheData1);
        self::assertEquals(expected: $apiData2, actual: $cacheData2);
        self::assertNotEquals(expected: $apiData1, actual: $apiData2);
        self::assertNotEquals(expected: $cacheData1, actual: $cacheData2);
    }

    /**
     * Assert getById() returns a payment method by its ID.
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
    public function testGetByIdFindResult(): void
    {
        $paymentMethods = Repository::getPaymentMethods(
            storeId: $this->storeId
        )->toArray();

        if (!isset($paymentMethods[0])) {
            self::fail(message: 'No payment methods found');
        }

        $paymentMethod = Repository::getById(
            storeId: $this->storeId,
            paymentMethodId: $paymentMethods[0]->id
        );

        self::assertNotNull(actual: $paymentMethod);
        self::assertEquals(
            expected: $paymentMethods[0]->id,
            actual: $paymentMethod->id
        );
    }

    /**
     * Assert getById() returns NULL when no payment method is found.
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
    public function testGetByIdReturnsNull(): void
    {
        $paymentMethods = Repository::getPaymentMethods(
            storeId: $this->storeId
        )->toArray();

        if (!isset($paymentMethods[0])) {
            self::fail(message: 'No payment methods found');
        }

        $paymentMethod = Repository::getById(
            storeId: $this->storeId,
            paymentMethodId: 'Not-a-Method'
        );

        self::assertNull(actual: $paymentMethod);
    }
}
