<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod;

use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
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
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\Type as PaymentMethodType;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\EcomTest\Utilities\DummySettingsReader;
use Throwable;
use ValueError;

/**
 * Integration tests for PaymentMethods repository.
 */
#[AllowMockObjectsWithoutExpectations]
class RepositoryTest extends TestCase
{
    private Cache $cache;

    /**
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     * @throws AttributeCombinationException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new Filesystem(
                path: '/tmp/ecom-test/paymentMethods/' . time()
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID'],
            settingsReader: new DummySettingsReader(cacheEnabled: true)
        );

        $this->cache = Repository::getCache();
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testClearCache(): void
    {
        Repository::getPaymentMethods();

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
        $paymentMethods = Repository::getPaymentMethods();
        // Iterates through the existing payment method types and checks for any new additions from Resurs Bank.
        // If new types are detected, this will trigger exceptions in pipelines to alert us about it.
        $this->getPaymentMethodTypes(
            paymentMethods: $paymentMethods,
            enumType: ''
        );
        $this->assertNotEmpty(actual: $paymentMethods);
    }

    /**
     * Test behaviours of non existent payment method types.
     */
    public function testNonExistentMethodType(): void
    {
        $this->expectException(exception: ValueError::class);
        $this->getPaymentMethodTypes(
            paymentMethods: null,
            enumType: 'NON_EXISTENT_MEtHOD_TYPE'
        );
    }

    /**
     * Test payment method types and throw exception on nonexistent (new types) from Resurs.
     *
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function getPaymentMethodTypes(?PaymentMethodCollection $paymentMethods, string $enumType = ''): void
    {
        if ($enumType !== '') {
            // Specifically test a type.
            PaymentMethodType::from(value: (string)$enumType);
            return;
        }

        if ($paymentMethods === null) {
            return;
        }

        /** @var PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethodType::from(value: $paymentMethod->type->value);
        }
    }

    /**
     * Verify that getPaymentMethods properly caches data.
     *
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testGetPaymentMethodsReturnsCache(): void
    {
        $this->assertEmpty(actual: $this->cache->read());

        $data = Repository::getPaymentMethods();

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
     * Verify that different amounts result in different outputs.
     *
     * Assert different datasets from the API for different amount values. Also
     * make sure the cache is kept separated by the same value.
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
    public function testDataSeparatedByAmount(): void
    {
        $amount1 = 11;
        $amount2 = 1000;

        // Load data from API to cache.
        $apiData1 = Repository::getPaymentMethods(amount: $amount1)->toArray();

        $apiData2 = Repository::getPaymentMethods(amount: $amount2)->toArray();

        // Retrieve same data from cache.
        $cacheData1 = Repository::getCache(amount: $amount1)->read();

        self::assertNotNull(actual: $cacheData1);

        $cacheData1 = $cacheData1->toArray();

        $cacheData2 = Repository::getCache(amount: $amount2)->read();

        self::assertNotNull(actual: $cacheData2);

        $cacheData2 = $cacheData2->toArray();

        $this->assertEquals(expected: $apiData1, actual: $cacheData1);
        $this->assertEquals(expected: $apiData2, actual: $cacheData2);
        $this->assertNotEquals(expected: $apiData1, actual: $apiData2);
        $this->assertNotEquals(expected: $cacheData1, actual: $cacheData2);
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testGetByIdFindResult(): void
    {
        $paymentMethods = Repository::getPaymentMethods()->toArray();

        /** @var PaymentMethod|null $method */
        $method = $paymentMethods[0] ?? null;

        $this->assertNotNull(actual: $method);

        $paymentMethod = Repository::getById(paymentMethodId: $method->id);

        $this->assertNotNull(actual: $paymentMethod);
        $this->assertEquals(expected: $method->id, actual: $paymentMethod->id);
    }

    /**
     * Assert getById() returns NULL when no payment method is found.
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
    public function testGetByIdReturnsNull(): void
    {
        $paymentMethods = Repository::getPaymentMethods()->toArray();

        if (!isset($paymentMethods[0])) {
            $this->fail(message: 'No payment methods found');
        }

        $paymentMethod = Repository::getById(paymentMethodId: 'Not-a-Method');

        $this->assertNull(actual: $paymentMethod);
    }
}
