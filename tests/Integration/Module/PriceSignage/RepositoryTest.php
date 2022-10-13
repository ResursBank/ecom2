<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PriceSignage;

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
use Resursbank\Ecom\Module\PriceSignage\Models\PriceSignage;
use Resursbank\Ecom\Module\PriceSignage\Repository;
use Resursbank\Ecom\Lib\Repository\Cache;

/**
 * Integration tests for PriceSignage repository.
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
     * @var string
     */
    private string $paymentMethodId;

    /**
     * @var float
     */
    private float $amount = 1000.00;

    /**
     * @return void
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        $this->storeId = (string) $_ENV['STORE_ID'];
        $this->paymentMethodId = (string) $_ENV['ANNUITY_PAYMENT_METHOD_ID'];

        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: new Filesystem(path: '/tmp/ecom-test/priceSignage/' . time()),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        $this->cache = $this->getCache();
        $this->cache->clear();

        parent::setUp();
    }

    /**
     * @param string|null $paymentMethodId
     * @param float|null $amount
     * @param int|null $monthFilter
     * @return Cache
     * @throws IllegalValueException
     */
    private function getCache(
        ?string $paymentMethodId = null,
        ?float $amount = null,
        ?int $monthFilter = null
    ): Cache {
        return Repository::getCache(
            storeId: $this->storeId,
            paymentMethodId: $paymentMethodId ?? $this->paymentMethodId,
            amount: $amount ?? $this->amount,
            monthFilter: $monthFilter
        );
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
        Repository::getPriceSignage(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount
        );

        self::assertNotNull(actual: $this->cache->read());

        $this->cache->clear();

        self::assertNull(actual: $this->cache->read());
    }

    /**
     * Assert getPriceSignage() returns data from the API when cache is empty.
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
    public function testGetPriceSignageReturnsWithoutCache(): void
    {
        self::assertNull(actual: $this->cache->read());
        self::assertNotEmpty(
            actual: Repository::getPriceSignage(
                storeId: $this->storeId,
                paymentMethodId: $this->paymentMethodId,
                amount: $this->amount
            )
        );
    }

    /**
     * Assert getPriceSignage() retrieves payment methods, priceSignage them in
     * cache, and will later return the same priceSignage from cache.
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
    public function testGetPriceSignageReturnsCache(): void
    {
        self::assertEmpty(actual: $this->cache->read());

        $data = Repository::getPriceSignage(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount
        );

        self::assertNotEmpty(actual: $data);

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        self::assertEquals(expected: $data, actual: $this->cache->read());
    }

    /**
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws AuthException
     * @throws CurlException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ApiException
     * @throws ReflectionException
     * @throws CacheException
     */
    public function testGetPriceSignageFilterByMonth(): void
    {
        $months1 = 3;
        $months2 = 12;

        $cache1 = $this->getCache(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount,
            monthFilter: $months1
        );

        $cache2 = $this->getCache(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount,
            monthFilter: $months2
        );

        $cache1->clear();
        $cache2->clear();

        self::assertNull(actual: $cache1->read());
        self::assertNull(actual: $cache2->read());

        $noCacheResponse1 = Repository::getPriceSignage(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount,
            monthFilter: $months1
        );

        $noCacheResponse2 = Repository::getPriceSignage(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount,
            monthFilter: $months2
        );

        self::assertCount(
            expectedCount: 1,
            haystack: $noCacheResponse1->costList,
            message: "Response should be filtered by $months1 months."
        );

        self::assertCount(
            expectedCount: 1,
            haystack: $noCacheResponse2->costList,
            message: "Response should be filtered by $months2 months."
        );

        $cacheData1 = $cache1->read();
        $cacheData2 = $cache2->read();

        self::assertInstanceOf(
            expected: PriceSignage::class,
            actual: $cacheData1,
            message: 'The cache should contain a PriceSignage object.'
        );

        self::assertInstanceOf(
            expected: PriceSignage::class,
            actual: $cacheData2,
            message: 'The cache should contain a PriceSignage object.'
        );

        self::assertCount(
            expectedCount: 1,
            haystack: $cacheData1->costList,
            message: "Cache should be filtered by $months1 months."
        );

        self::assertCount(
            expectedCount: 1,
            haystack: $cacheData2->costList,
            message: "Cache should be filtered by $months2 months."
        );

        self::assertSame(
            expected: $months1,
            actual: $cacheData1->costList[0]->months,
            message: "Cache should be filtered by $months1 months."
        );

        self::assertSame(
            expected: $months2,
            actual: $cacheData2->costList[0]->months,
            message: "Cache should be filtered by $months2 months."
        );
    }
}
