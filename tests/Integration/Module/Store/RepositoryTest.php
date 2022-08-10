<?php

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
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Store\Api\GetStores;
use Resursbank\Ecom\Module\Store\Repository;

/**
 * Test API call to get stores.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    private const TMP_LOG_DIR = '/tmp/ecom-test/stores/log';
    private const TMP_CACHE_FILE = '/tmp/ecom-test/stores/cache';

    /**
     * @var CacheInterface $cache
     */
    private CacheInterface $cache;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * @return void
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        $this->cache = new Filesystem(path: self::TMP_CACHE_FILE);
        $this->logger = new FileLogger(path: self::TMP_LOG_DIR);

        // Always clear cache between tests.
        $this->cache->clear(key: self::TMP_CACHE_FILE);

        Config::setup(
            logger: $this->logger,
            cache: $this->cache,
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
     * Assert read() retrieves stores, store them in cache, and will later
     * return the same stores from cache.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     */
    public function testReadReturnsCache(): void
    {
        $data = Repository::read();

        self::assertNotEmpty(actual: $data);

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        self::assertSame(expected: $data, actual: Repository::readCache());
    }
}
