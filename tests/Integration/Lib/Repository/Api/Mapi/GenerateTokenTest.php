<?php

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Repository\Api\Mapi;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\GenerateToken;

/**
* Test for JWT token generation.
*
* @psalm-suppress PropertyNotSetInConstructor
*/
class GenerateTokenTest extends TestCase
{
    /**
     * Assert JWT token is generated during request.
     *
     * @return void
     * @throws AuthException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws CurlException
     * @throws ValidationException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testJwtTokenGenerates(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        if (Config::getJwtAuth() === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $token = (new GenerateToken(auth: Config::getJwtAuth()))->call();
        $currentTime = time();

        self::assertSame(
            expected: 'Bearer',
            actual: $token->token_type
        );
        self::assertGreaterThan(
            expected: $currentTime,
            actual: $token->expires_in
        );
    }

    /**
     * Assert AuthException is thrown when JWT auth has invalid client id.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testInvalidClientIdThrows(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: new Jwt(
                clientId: 'foo',
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        if (Config::getJwtAuth() === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $this->expectException(exception: AuthException::class);

        (new GenerateToken(auth: Config::getJwtAuth()))->call();
    }

    /**
     * Assert AuthException is thrown when JWT auth has invalid client secret.
     *
     * @return void
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testInvalidClientSecretThrows(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: 'bar',
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        if (Config::getJwtAuth() === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $this->expectException(exception: AuthException::class);

        (new GenerateToken(auth: Config::getJwtAuth()))->call();
    }
}
