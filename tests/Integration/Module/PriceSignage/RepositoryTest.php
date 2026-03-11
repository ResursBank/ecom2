<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PriceSignage;

use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
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
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\Model\PriceSignage\PriceSignage;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\PriceSignage\Repository;
use Throwable;

/**
 * Integration tests for PriceSignage repository.
 */
#[AllowMockObjectsWithoutExpectations]
class RepositoryTest extends TestCase
{
    private Cache $cache;

    private string $paymentMethodId;

    private float $amount = 1000.00;

    /**
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    protected function setUp(): void
    {
        $this->paymentMethodId = $_ENV['ANNUITY_PAYMENT_METHOD_ID'];

        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new Filesystem(
                path: '/tmp/ecom-test/priceSignage/' . time()
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        $this->cache = $this->getCache();
        $this->cache->clear();

        parent::setUp();
    }

    /**
     * @throws IllegalValueException
     */
    private function getCache(
        ?string $paymentMethodId = null,
        ?float $amount = null,
        ?int $monthFilter = null
    ): Cache {
        return Repository::getCache(
            paymentMethodId: $paymentMethodId ?? $this->paymentMethodId,
            amount: $amount ?? $this->amount,
            monthFilter: $monthFilter
        );
    }

    /**
     * Assert clearCache() clears cache.
     *
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
     * @throws ConfigException
     * @throws Throwable
     */
    public function testClearCache(): void
    {
        Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount
        );

        $this->assertNotNull(actual: $this->cache->read());

        $this->cache->clear();

        $this->assertNull(actual: $this->cache->read());
    }

    /**
     * Assert getPriceSignage() returns data from the API when cache is empty.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public function testGetPriceSignageReturnsWithoutCache(): void
    {
        $this->assertNull(actual: $this->cache->read());
        $this->assertNotEmpty(
            actual: Repository::getPriceSignage(
                paymentMethodId: $this->paymentMethodId,
                amount: $this->amount
            )
        );
    }

    /**
     * Assert getPriceSignage() retrieves payment methods, priceSignage them in
     * cache, and will later return the same priceSignage from cache.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public function testGetPriceSignageReturnsCache(): void
    {
        $this->assertEmpty(actual: $this->cache->read());

        $data = Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount
        );

        $this->assertNotEmpty(actual: $data);

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        $this->assertEquals(expected: $data, actual: $this->cache->read());
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
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

        $this->assertNull(actual: $cache1->read());
        $this->assertNull(actual: $cache2->read());

        $noCacheResponse1 = Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount,
            monthFilter: $months1
        );

        $noCacheResponse2 = Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount,
            monthFilter: $months2
        );

        $this->assertCount(
            expectedCount: 1,
            haystack: $noCacheResponse1->costList,
            message: "Response should be filtered by $months1 months."
        );

        $this->assertCount(
            expectedCount: 1,
            haystack: $noCacheResponse2->costList,
            message: "Response should be filtered by $months2 months."
        );

        /** @var PriceSignage $cacheData1 */
        $cacheData1 = $cache1->read();

        /** @var PriceSignage $cacheData2 */
        $cacheData2 = $cache2->read();

        $this->assertInstanceOf(
            expected: PriceSignage::class,
            actual: $cacheData1,
            message: 'The cache should contain a PriceSignage object.'
        );

        $this->assertInstanceOf(
            expected: PriceSignage::class,
            actual: $cacheData2,
            message: 'The cache should contain a PriceSignage object.'
        );

        $this->assertCount(
            expectedCount: 1,
            haystack: $cacheData1->costList,
            message: "Cache should be filtered by $months1 months."
        );

        $this->assertCount(
            expectedCount: 1,
            haystack: $cacheData2->costList,
            message: "Cache should be filtered by $months2 months."
        );

        $cost1 = $cacheData1->costList[0];
        $cost2 = $cacheData2->costList[0];

        $this->assertInstanceOf(expected: Cost::class, actual: $cost1);
        $this->assertInstanceOf(expected: Cost::class, actual: $cost2);

        $this->assertSame(
            expected: $months1,
            actual: $cost1->durationMonths,
            message: "Cache should be filtered by $months1 months."
        );

        $this->assertSame(
            expected: $months2,
            actual: $cost2->durationMonths,
            message: "Cache should be filtered by $months2 months."
        );
    }

    /**
     * Assert getPriceSignage() throws if the supplied amount is too low.
     *
     * Should throw if the supplied amount is less than supplied payment method
     * minimum. purchase amount.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public function testGetPriceSignageThrowsWithLowAmount(): void
    {
        $this->expectException(exception: CurlException::class);

        Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethodId,
            amount: 0.1
        );
    }

    /**
     * Assert that getCache returns cached data if it should exist.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetCache(): void
    {
        // Attempt to fetch without cached data
        $this->cache->clear();
        $cached = Repository::getCache(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount
        )->read();

        if ($cached !== null) {
            $this->fail(message: 'Returned cache object is not null');
        } else {
            $this->addToAssertionCount(count: 1);
        }

        // Make request
        Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount
        );

        // Attempt to fetch now that data should be cached
        $cached = Repository::getCache(
            paymentMethodId: $this->paymentMethodId,
            amount: $this->amount
        )->read();

        if ($cached === null) {
            $this->fail(message: 'Returned cache object is not null');
        } else {
            $this->addToAssertionCount(count: 1);
        }
    }
}
