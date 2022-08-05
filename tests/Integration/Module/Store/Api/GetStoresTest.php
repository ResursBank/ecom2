<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Store\Api;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
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
     * Assert exec() retrieves as many stores as we request.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TypeException
     * @throws ValidationException
     */
    public function testExecSize(): void
    {
        self::assertCount(
            expectedCount: 10,
            haystack: $this->api->exec(size: 10)
        );
    }

    /**
     * Assert exec() retrieves all stores by default.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TypeException
     * @throws ValidationException
     */
    public function testExecFetchAll(): void
    {
        self::assertNotCount(
            expectedCount: 0,
            haystack: $this->api->exec()
        );
    }

    /**
     * Assert exec() can retrieve individual sections using the page argument.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TypeException
     * @throws ValidationException
     */
    public function testExecPagination(): void
    {
        $page1 = $this->api->exec(size: 5, page: 0);
        $page2 = $this->api->exec(size: 5, page: 1);

        self::assertCount(expectedCount: 5, haystack: $page1);
        self::assertCount(expectedCount: 5, haystack: $page2);
        self::assertNotEquals(expected: $page1, actual: $page2);
    }
}
