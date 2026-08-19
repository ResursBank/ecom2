<?php

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Repository\Api\Mapi;

use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
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
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\GenerateToken;
use Resursbank\EcomTest\Utilities\DummySettingsReader;

/**
* Test for JWT token generation.
*/
#[AllowMockObjectsWithoutExpectations]
class GenerateTokenTest extends TestCase
{
    /**
     * Assert JWT token is generated during request.
     *
     * @throws AuthException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws CurlException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testJwtTokenGenerates(): void
    {
        Config::setup(
            logger: $this->createMock(type: FileLogger::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID'],
            settingsReader: new DummySettingsReader()
        );

        $auth = Config::getJwtAuth();

        if ($auth === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $token = (new GenerateToken(auth: $auth))->call();
        $currentTime = time();

        self::assertSame(expected: 'Bearer', actual: $token->token_type);

        self::assertGreaterThan(
            minimum: $currentTime,
            actual: $token->expires_at
        );
    }

    /**
     * Assert AuthException is thrown when JWT auth has invalid client id.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testInvalidClientIdThrows(): void
    {
        Config::setup(
            logger: $this->createMock(type: FileLogger::class),
            jwtAuth: new Jwt(
                clientId: 'foo',
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        $auth = Config::getJwtAuth();

        if ($auth === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $this->expectException(exception: AuthException::class);

        (new GenerateToken(auth: $auth))->call();
    }

    /**
     * Assert AuthException is thrown when JWT auth has invalid client secret.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testInvalidClientSecretThrows(): void
    {
        Config::setup(
            logger: $this->createMock(type: FileLogger::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: 'bar',
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        $auth = Config::getJwtAuth();

        if ($auth === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $this->expectException(exception: AuthException::class);

        (new GenerateToken(auth: $auth))->call();
    }
}
