<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco\Repository;

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
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\Rco\Repository\PaymentMethods as Repository;
use Throwable;

/**
 * Integration tests for PaymentMethods repository.
 */
class PaymentMethodsTest extends TestCase
{
    private Cache $cache;

    private string $storeId;

    /**
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    protected function setUp(): void
    {
        $this->storeId = $_ENV['STORE_ID'];

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(
                path: '/tmp/ecom-test/rco/paymentMethods/' . time()
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['RCO_JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['RCO_JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['RCO_JWT_AUTH_SCOPE']),
                grantType: GrantType::from(
                    value: $_ENV['RCO_JWT_AUTH_GRANT_TYPE']
                )
            )
        );

        $this->cache = Repository::getCache(storeId: $this->storeId);
        $this->cache->clear();

        parent::setUp();
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
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public function testClearCache(): void
    {
        Repository::getPaymentMethods(storeId: $this->storeId);

        $this->assertNotNull(actual: $this->cache->read());

        $this->cache->clear();

        $this->assertNull(actual: $this->cache->read());
    }

    /**
     * Assert getPaymentMethods() returns data from the API when cache is empty.
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
    public function testGetPaymentMethodsReturnsWithoutCache(): void
    {
        $this->assertNull(actual: $this->cache->read());
        $this->assertNotEmpty(
            actual: Repository::getPaymentMethods(
                storeId: $this->storeId
            )
        );
    }

    /**
     * Assert getPaymentMethods() retrieves payment methods, paymentMethod them
     * in cache, and will later return the same paymentMethods from cache.
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
    public function testGetPaymentMethodsReturnsCache(): void
    {
        $this->assertEmpty(actual: $this->cache->read());

        $data = Repository::getPaymentMethods(storeId: $this->storeId);

        $this->assertNotEmpty(actual: $data);

        $data->rewind();

        /* Since we cannot mock the API adapter we will need to call the
            readCache() directly to ensure we don't fetch from the API again. */
        $this->assertEquals(
            expected: $data,
            actual: $this->cache->read()
        );
    }

    /**
     * Assert getById() returns a payment method by its ID.
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
    public function testGetByIdFindResult(): void
    {
        $paymentMethods = Repository::getPaymentMethods(
            storeId: $this->storeId
        )->toArray();

        /** @var PaymentMethod|null $method */
        $method = $paymentMethods[0] ?? null;

        $this->assertNotNull(actual: $method);

        $paymentMethod = Repository::getById(
            storeId: $this->storeId,
            paymentMethodId: $method->methodId
        );

        $this->assertNotNull(actual: $paymentMethod);
        $this->assertEquals(
            expected: $method->methodId,
            actual: $paymentMethod->methodId
        );
    }

    /**
     * Assert getById() returns NULL when no payment method is found.
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
    public function testGetByIdReturnsNull(): void
    {
        $paymentMethods = Repository::getPaymentMethods(
            storeId: $this->storeId
        )->toArray();

        if (!isset($paymentMethods[0])) {
            $this->fail(message: 'No payment methods found');
        }

        $paymentMethod = Repository::getById(
            storeId: $this->storeId,
            paymentMethodId: 'Not-a-Method'
        );

        $this->assertNull(actual: $paymentMethod);
    }
}
