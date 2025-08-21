<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethodList;

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
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodTypeMap;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodTypeMapCollection;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Resursbank\Ecom\Module\PaymentMethodList\Repository;
use Throwable;

/**
 * Integration tests for PaymentMethods repository.
 */
class RepositoryTest extends TestCase
{
    private Cache $cache;

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
     * @throws Throwable
     * @throws ValidationException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(
                path: '/tmp/ecom-test/paymentMethods/' . time()
            ),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['RWS_STORE_ID']
        );

        $this->cache = Repository::getCache(
            paymentMethods: PaymentMethodRepository::getPaymentMethods()
        );
        $this->cache->clear();

        parent::setUp();
    }

    /**
     * Checks that list of collected payment methods types from RWS contains an
     * entry matching the supplied payment method ID from MAPI.
     *
     * This has been separated to reduce cognitive complexity.
     */
    private function hasTypeMapEntry(
        string $id,
        PaymentMethodTypeMapCollection $types
    ): bool {
        /** @var PaymentMethodTypeMap $type */
        foreach ($types as $type) {
            if ($type->paymentMethodId === $id) {
                $this->addToAssertionCount(1);
                return true;
            }
        }

        return false;
    }

    /**
     * Assert we can get a full collection of payment methods, submit this to
     * RWS and get a response back with the same payment methods.
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
    public function testGetPaymentMethodTypes(): void
    {
        // Get payment methods.
        $paymentMethods = PaymentMethodRepository::getPaymentMethods();

        $this->assertGreaterThan(
            expected: 0,
            actual: $paymentMethods->count()
        );

        // Get types.
        $paymentMethodTypes = Repository::getPaymentMethodTypes(
            paymentMethods: $paymentMethods
        );

        $this->assertGreaterThan(
            expected: 0,
            actual: $paymentMethodTypes->count()
        );

        // Confirm we got a type for each payment method.

        /** @var PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            if (
                $this->hasTypeMapEntry(
                    id: $paymentMethod->id,
                    types: $paymentMethodTypes
                )
            ) {
                continue;
            }

            $this->fail(
                message: 'No type found for payment method: ' .
                $paymentMethod->id
            );
        }
    }
}
