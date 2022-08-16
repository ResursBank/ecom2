<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Store\Api;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Store\Api\GetStores;

/**
 * Test API call to get stores.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetStoresTest extends TestCase
{
    private GetStores $api;

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

        $this->api = new GetStores();

        parent::setUp();
    }

    /**
     * Assert call() retrieves as many stores as we request.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalTypeException
     * @throws ValidationException
     */
    public function testCallSize(): void
    {
        self::assertCount(
            expectedCount: 10,
            haystack: $this->api->call(size: 10)
        );
    }

    /**
     * Assert call() retrieves all stores by default.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalTypeException
     * @throws ValidationException
     */
    public function testCallFetchAll(): void
    {
        self::assertNotEmpty(actual: $this->api->call());
    }

    /**
     * Assert call() can retrieve individual sections using the page argument.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCallPagination(): void
    {
        $page1 = $this->api->call(size: 5, page: 0);
        $page2 = $this->api->call(size: 5, page: 1);

        self::assertCount(expectedCount: 5, haystack: $page1);
        self::assertCount(expectedCount: 5, haystack: $page2);
        self::assertNotEquals(expected: $page1, actual: $page2);
    }
}
