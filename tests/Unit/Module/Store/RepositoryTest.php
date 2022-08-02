<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Lib\Api\Credentials;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Module\PaymentMethod\Api\GetPaymentMethods\Request;
use Resursbank\Ecom\Module\PaymentMethod\Api\GetPaymentMethods\Response;
use Resursbank\Ecom\Module\PaymentMethod\Model\Method;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\EcomTest\Data\ApiResponse\GetPaymentMethods;
use stdClass;
use function is_array;
use function json_encode;

/**
 * Test business logic of payment methods Repository class.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
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
     * @var Request
     */
    private Request $request;

    /**
     * We execute the actual Config::setup() method to initiate mocked objects
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
        $this->request = $this->createMock(
            originalClassName: Request::class
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
     * Helper method to assert number of times Config::$instance->cache->read()
     * is called.
     *
     * @param bool $once
     * @return self
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     * @noinspection PhpSameParameterValueInspection
     */
    private function expectReadCache(
        bool $once = true
    ): self {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->noneCache
            ->expects($once ? self::once() : self::any())
            ->method(constraint: 'read');

        return $this;
    }

    /**
     * Helper method to assert number of times
     * Config::$instance->logger->debug() is called.
     *
     * @param bool $once
     * @return self
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     * @noinspection PhpSameParameterValueInspection
     */
    private function expectDebugLog(
        bool $once = false
    ): self {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->logger
            ->expects($once ? self::once() : self::any())
            ->method(constraint: 'debug');

        return $this;
    }

    /**
     * Helper method to assert number of times the API request is executed.
     *
     * @param bool $once
     * @return self
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     * @noinspection PhpSameParameterValueInspection
     */
    private function expectRequest(
        bool $once = true
    ): self {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->request
            ->expects($once ? self::once() : self::any())
            ->method(constraint: 'execute');

        return $this;
    }

    /**
     * Helper method to assert that Config::$instance->logger->debug() is never
     * called.
     *
     * @return self
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     * @noinspection PhpSameParameterValueInspection
     */
    private function expectDebugLogNever(): self
    {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->logger
            ->expects(self::never())
            ->method(constraint: 'debug');

        return $this;
    }

    /**
     * Helper method to assert API request is never performed.
     *
     * @return self
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     * @noinspection PhpSameParameterValueInspection
     */
    private function expectRequestNever(): self
    {
        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @noinspection PhpArgumentWithoutNamedIdentifierInspection
         * @phpstan-ignore-next-line
         */
        $this->request
            ->expects(self::never())
            ->method(constraint: 'execute');

        return $this;
    }

    /**
     * Assertions to validate parsed payment method data.
     *
     * @param array|null $data
     * @return void'
     */
    private function validateAcceptedData(
        ?array $data
    ): void {
        self::assertIsArray(actual: $data);
        self::assertNotEmpty(actual: $data);

        foreach ($data as $item) {
            self::assertInstanceOf(expected: Method::class, actual: $item);
        }
    }

    /**
     * Assert Module::read() returns valid cache date without calling API.
     *
     * @return void
     * @throws JsonException
     * @throws ApiException
     * @throws CacheException
     */
    public function testReadReturnsCache(): void
    {
        $this->setCacheReadReturn(data: GetPaymentMethods::$data)
            ->expectReadCache()
            ->expectDebugLogNever()
            ->expectRequestNever()
            ->validateAcceptedData(
                data: Repository::read(request: $this->request)
            );
    }

    /**
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadForwardsCacheException(): void
    {
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: ['5', '6'])
            ->expectReadCache()
            ->expectRequestNever()
            ->expectDebugLog();

        Repository::read(request: $this->request, silent: false);
    }

    /**
     * Assert Module::read() calls API without valid cache data.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCallsApiWithoutCache(): void
    {
        $this->setCacheReadReturn()
            ->expectReadCache()
            ->expectDebugLogNever()
            ->expectRequest()
            ->setRequestReturn(data: GetPaymentMethods::getMethods())
            ->validateAcceptedData(
                data: Repository::read(request: $this->request)
            );
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return NULL when cache->read() returns NULL.
     * 3. No Exception should occur, hence debug log should never be called.
     *
     * @return void
     * @throws JsonException
     * @throws CacheException
     */
    public function testReadCacheReturnsNullWithoutCache(): void
    {
        $this->setCacheReadReturn()
            ->expectReadCache()
            ->expectDebugLogNever();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return NULL when cache->read() returns data
     * that hasn't been JSON encoded.
     * 3. A JsonException will occur, debug log will be called.
     *
     * @return void
     * @throws JsonException
     * @throws CacheException
     */
    public function testReadCacheReturnsNullWithoutValidJson(): void
    {
        $this->setCacheReadReturn(data: 'some-corrupt-data')
            ->expectReadCache()
            ->expectDebugLog();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return NULL when cache->read() returns JSON
     * encoded data that does not decode to an array.
     * 3. A ValidationException will occur, debug log will be called.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheReturnsNullWithoutArray(): void
    {
        $this->setCacheReadReturn(
            data: json_encode(
                value: 'My big harmony',
                flags: JSON_THROW_ON_ERROR
            )
        )->expectReadCache()->expectDebugLog();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return NULL when cache->read() returns JSON
     * encoded empty array.
     * 3. A ValidationException will occur, debug log will be called.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheReturnsNullWithEmptyArray(): void
    {
        $this->setCacheReadReturn(data: [])
            ->expectReadCache()
            ->expectDebugLog();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return NULL when cache->read() returns a JSON
     * encoded array containing a sequential array.
     * 3. A ValidationException will occur, debug log will be called.
     *
     * This ensures that if invalid data is inserted in the middle of our cache
     * the entire cache will be considered corrupt.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     * @throws TestException
     */
    public function testReadCacheReturnsNullWithInvalidElements(): void
    {
        $this->setCacheReadReturn(
            data: array_merge(GetPaymentMethods::getMethods(), ['test', 'tes'])
        )
            ->expectReadCache()
            ->expectDebugLog();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return NULL when cache->read() returns a one
     * dimensional associative array.
     * 3. A ValidationException will occur, debug log will be called.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheReturnsNullWithAssocArray(): void
    {
        $this->setCacheReadReturn(data: ['id' => 'epic'])
            ->expectReadCache()
            ->expectDebugLog();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return NULL when cache->read() returns a one
     * dimensional sequential array containing invalid data types.
     * 3. A ValidationException will occur, debug log will be called.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheReturnsNullWithInvalidSequentialArray(): void
    {
        $this->setCacheReadReturn(data: ['id', 'epic', 5])
            ->expectReadCache()
            ->expectDebugLog();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert that:
     *
     * 1. Module::readCache() will call Config::$instance->cache->read() once.
     * 2. Module::readCache() will return array of Method instances.
     * 3. No Exception occurs, debug log is never called.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheReturnsArray(): void
    {
        $this->setCacheReadReturn(data: GetPaymentMethods::$data)
            ->expectReadCache()
            ->expectDebugLogNever()
            ->validateAcceptedData(data: Repository::readCache());
    }

    /**
     * Assert readCache() converts JsonException to a plain CacheException after
     * logging the actual exception that occurred.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheConvertsJsonException(): void
    {
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: 'LAMBADA!')
            ->expectReadCache()
            ->expectDebugLog();

        Repository::readCache(silent: false);
    }

    /**
     * Assert readCache() converts ValidationException to a plain CacheException
     * after logging the actual exception that occurred.
     *
     * @return void
     * @throws CacheException
     * @throws JsonException
     */
    public function testReadCacheConvertsValidationException(): void
    {
        $this->expectException(exception: CacheException::class);
        $this->setCacheReadReturn(data: ['5', '6'])
            ->expectReadCache()
            ->expectDebugLog();

        Repository::readCache(silent: false);
    }

//    public function testReadApiReturnsNull(): void
//    {
//        $this->
//    }
}
