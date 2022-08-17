<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod\Api;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentMethod\Api\GetPaymentMethods;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;

/**
 * Test API call to get payment methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetPaymentMethodTest extends TestCase
{
    private GetPaymentMethods $api;

    /**
     * @return void
     * @throws EmptyValueException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        $this->api = new GetPaymentMethods();

        parent::setUp();
    }

    /**
     * @return Store
     * @throws ApiException
     * @throws CacheException
     * @psalm-suppress MixedInferredReturnType
     */
    private function getRandomStore(): Store
    {
        $stores = StoreRepository::getStores()->toArray();

        /** @psalm-suppress MixedReturnType */
        return $stores[(int) array_rand(array: $stores)];
    }

    /**
     * Assert call() reflects disabled methods based on supplied amount
     * threshold.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCallAmount(): void
    {
        $methods = $this->api->call(
            storeId: $this->getRandomStore()->id,
            amount: 1
        );

        /** @var PaymentMethod $method */
        foreach ($methods as $method) {
            if ($method->minPurchaseLimit > 1) {
                self::assertTrue(condition: $method->status->disabled);
                self::assertContains(
                    needle: 'AMOUNT_NOT_MATCHING',
                    haystack: $method->status->disabledReasons
                );
            } else {
                self::assertFalse(condition: $method->status->disabled);
            }
        }
    }

    /**
     * Assert call() retrieves all paymentMethods by default.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCallFetchAll(): void
    {
        self::assertNotEmpty(actual: $this->api->call(
            storeId: $this->getRandomStore()->id
        ));
    }
}
