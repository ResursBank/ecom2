<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Store;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Module\Store\Api\GetStores as Api;
use Resursbank\Ecom\Module\Store\Models\StoreCollection;
use Resursbank\Ecom\Module\Store\Repository;
use Resursbank\EcomTest\Data\GetStores;
use TypeError;

use function is_array;
use function json_encode;

/**
 * Test business logic of payment methods Repository class.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @todo We cannot test that readCache() converts ReflectionException because we cannot mock DataConverter.
 */
class RepositoryTest extends TestCase
{
    /**
     * @var None
     */
    private None $noneCache;

    /**
     * @var FileLogger
     */
    private FileLogger $logger;

    /**
     * @var Api
     */
    private Api $api;

    /**
     * We callute the actual Config::setup() method to initiate mocked objects
     * to be utilised in tests against the static methods available on our
     * subject class. The methods on our subject class (such as readCache())
     * will make calls to object such as Config::$instance->cache, and we wish
     * to test behaviour when the results from the API / Cache differ.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->noneCache = $this->createMock(
            originalClassName: None::class
        );
        $this->logger = $this->createMock(
            originalClassName: FileLogger::class
        );
        $this->api = $this->createMock(
            originalClassName: Api::class
        );
        Config::setup(
            logger: $this->logger,
            cache: $this->noneCache
        );

        parent::setUp();
    }

    /**
     * Helper method to assign result from Config::$instance->cache->read()
     *
     * @param mixed $data
     * @return self
     * @throws JsonException
     */
    private function setCacheReadReturn(
        mixed $data = null
    ): self {
        /** @psalm-suppress MixedAssignment */
        $converted = $data;

        if (is_array(value: $data)) {
            $converted = json_encode(value: $data, flags: JSON_THROW_ON_ERROR);
        }

        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @phpstan-ignore-next-line
         */
        $this->noneCache->method('read')->willReturn(value: $converted);

        return $this;
    }

    /**
     * Helper method to assign result from $this->api->call()
     *
     * @param StoreCollection $stores
     * @return self
     */
    private function setApiCallReturn(
        StoreCollection $stores
    ): self {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @phpstan-ignore-next-line
         */
        $this->api->method('call')->willReturn(value: $stores);

        return $this;
    }

    /**
     * Helper method to assert number of times Config::$instance->cache->read()
     * is called.
     *
     * @param int $calls | -1 = any number of calls.
     * @return self
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     * @noinspection PhpSameParameterValueInspection
     */
    private function expectReadCache(
        int $calls
    ): self {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->noneCache
            ->expects($calls === -1 ? self::any() : self::exactly($calls))
            ->method(constraint: 'read');

        return $this;
    }

    /**
     * Helper method to assert number of times $this->api->call() is called.
     *
     * @param int $calls | -1 = any number of calls.
     * @return self
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     * @noinspection PhpSameParameterValueInspection
     */
    private function expectApiCall(
        int $calls
    ): self {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->api
            ->expects($calls === -1 ? self::any() : self::exactly($calls))
            ->method(constraint: 'call');

        return $this;
    }


    /**
     * Helper method to assert number of times
     * Config::$instance->logger->debug() is called.
     *
     * @param int $calls | -1 = any number of calls.
     * @return self
     * @noinspection PhpSameParameterValueInspection
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     */
    private function expectDebugLog(
        int $calls
    ): self {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->logger
            ->expects($calls === -1 ? self::any() : self::exactly($calls))
            ->method(constraint: 'debug');

        return $this;
    }

    /**
     * Assert Repository::getStores() returns cached data without calling the API
     * or calling the debug logger.
     *
     * @return void
     * @throws JsonException
     * @throws ApiException
     * @throws CacheException
     */
    public function testReadReturnsCache(): void
    {
        $this->setCacheReadReturn(data: GetStores::$data)
            ->expectReadCache(calls: 1)
            ->expectApiCall(calls: 0)
            ->expectDebugLog(calls: 0);

        $data = Repository::getStores();

        self::assertNotEmpty(actual: $data);
    }

    /**
     * Assert readCache() logs, converts and forwards TypeError as
     * CacheException when cache->read() throws TypeError (e.g. when cache is
     * not an array consisting of Store transmutable data).
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheConvertsTypeError(): void
    {
        $this->expectException(exception: TypeError::class);
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: ['5', '6'])
            ->expectReadCache(calls: 1)
            ->expectApiCall(calls: 0)
            ->expectDebugLog(calls: -1);

        Repository::getStores();
    }

    /**
     * Assert readCache() logs, converts and forwards IllegalTypeException as
     * CacheException when data is not an array.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadApiConvertsIllegalTypeException(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: 'big data')
            ->expectReadCache(calls: 1)
            ->expectApiCall(calls: 0)
            ->expectDebugLog(calls: -1);

        Repository::getStores();
    }

    /**
     * Assert readCache() logs, converts and forwards TypeError as
     * CacheException.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadForwardsCacheException(): void
    {
        $this->expectException(exception: TypeError::class);
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: ['5', '6'])
            ->expectReadCache(calls: 1)
            ->expectApiCall(calls: 0)
            ->expectDebugLog(calls: -1);

        Repository::getStores();
    }

    /**
     * Assert readApi() is called cache contains an empty array.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testReadCacheEmptyArrayReturnsNull(): void
    {
        $this->setCacheReadReturn(data: [])
            ->expectReadCache(calls: 1)
            ->setApiCallReturn(stores: GetStores::getStores())
            ->expectApiCall(calls: 1)
            ->expectDebugLog(calls: -1);

        Repository::getStores(api: $this->api);
    }

    /**
     * Assert readCache() throws JsonException when the cached data isn't JSON
     * encoded.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     */
    public function testReadCacheThrowsJsonException(): void
    {
        $this->expectException(exception: CacheException::class);

        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @phpstan-ignore-next-line
         */
        $this->noneCache->method('read')->willReturn(value: '27');

        $this->expectReadCache(calls: 1)
            ->expectApiCall(calls: 0)
            ->expectDebugLog(calls: -1);

        Repository::getStores(api: $this->api);
    }
}
