<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;

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
     * @return void
     * @throws EmptyValueException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
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

        Repository::clearCache();

        parent::setUp();
    }

    /**
     * @return Store
     * @throws ApiException
     * @throws CacheException
     */
    private function getRandomStore(): Store
    {
        $stores = StoreRepository::getStores()->toArray();

        /** @psalm-suppress MixedReturnType */
        return $stores[(int) array_rand(array: $stores)];
    }

    /**
     * Assert clearCache() clears cache.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     */
    public function testClearCache(): void
    {
        Repository::getPaymentMethods(storeId: $this->getRandomStore()->id);

        self::assertNotNull(actual: Repository::readCache());

        Repository::clearCache();

        self::assertNull(actual: Repository::readCache());
    }

    /**
     * Assert read() returns data from the API when cache is empty.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     */
    public function testReadReturnsWithoutCache(): void
    {
        self::assertNull(actual: Repository::readCache());
        self::assertNotEmpty(
            actual: Repository::getPaymentMethods(
                storeId: $this->getRandomStore()->id
            )
        );
    }

    /**
     * Assert read() retrieves payment methods, paymentMethod them in cache, and
     * will later return the same paymentMethods from cache.
     *
     * @return void
     * @throws ApiException
     * @throws CacheException
     */
    public function testReadReturnsCache(): void
    {
        self::assertEmpty(actual: Repository::readCache());

        $data = Repository::getPaymentMethods(
            storeId: $this->getRandomStore()->id
        );

        self::assertNotEmpty(actual: $data);

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        self::assertEquals(expected: $data, actual: Repository::readCache());
    }
}
