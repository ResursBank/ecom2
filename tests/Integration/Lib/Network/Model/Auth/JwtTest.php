<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network\Model\Auth;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;

/**
 * This class tests the features of the Jwt auth class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class JwtTest extends TestCase
{
    /**
     * Verify that fetching a Jwt token works as expected.
     *
     * @return void
     * @throws AuthException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetToken(): void
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

        if (Config::$instance->jwtAuth === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $token = Config::$instance->jwtAuth->getToken();
        $currentTime = time();

        self::assertSame(
            expected: 'Bearer',
            actual: $token->tokenType
        );
        self::assertGreaterThan(
            expected: $currentTime,
            actual: $token->validUntil
        );
    }

    /**
     * Verify that using invalid credentials will result in an exception being
     * thrown.
     *
     * @return void
     * @throws AuthException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testInvalidCredentials(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: new Jwt(
                clientId: 'foo',
                clientSecret: 'bar',
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        if (Config::$instance->jwtAuth === null) {
            self::fail(message: 'JWT auth is not configured');
        }

        $this->expectException(exception: AuthException::class);

        Config::$instance->jwtAuth->getToken();
    }
}
