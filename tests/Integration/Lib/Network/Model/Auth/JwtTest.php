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
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TypeException;
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
     * Verify that fetching a Jwt token works as expected
     *
     * @return void
     * @throws AuthException
     * @throws EmptyValueException
     * @throws TypeException
     */
    public function testGetToken(): void
    {
        if (isset($_ENV['JWT_AUTH_CLIENT_ID']) && isset($_ENV['JWT_AUTH_CLIENT_SECRET'])) {
            Config::setup(
                logger: $this->createMock(originalClassName: FileLogger::class),
                jwtAuth: new Jwt(
                    clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                    clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                    scope: $_ENV['JWT_AUTH_SCOPE'],
                    grantType: $_ENV['JWT_AUTH_GRANT_TYPE']
                )
            );

            $token = Config::$instance->jwtAuth->getToken();
            $currentTime = time();

            $this->assertEquals(
                expected: 'Bearer',
                actual: $token->tokenType
            );
            $this->assertGreaterThan(
                expected: $currentTime,
                actual: $token->validUntil
            );
        } else {
            $this->markTestSkipped('No JWT_AUTH_CLIENT_ID or JWT_AUTH_CLIENT_SECRET environment variables set');
        }
    }

    /**
     * Verify that using invalid credentials will result in an exception being thrown
     *
     * @return void
     * @throws AuthException
     * @throws EmptyValueException
     * @throws TypeException
     */
    public function testInvalidCredentials(): void
    {
        if (isset($_ENV['JWT_AUTH_SCOPE']) && isset($_ENV['JWT_AUTH_GRANT_TYPE'])) {
            Config::setup(
                logger: $this->createMock(originalClassName: FileLogger::class),
                jwtAuth: new Jwt(
                    clientId: 'foo',
                    clientSecret: 'bar',
                    scope: $_ENV['JWT_AUTH_SCOPE'],
                    grantType: $_ENV['JWT_AUTH_GRANT_TYPE']
                )
            );

            $this->expectException(exception: AuthException::class);

            Config::$instance->jwtAuth->getToken();
        } else {
            $this->markTestSkipped('No JWT_AUTH_SCOPE or JWT_AUTH_GRANT_TYPE environment variables set');
        }
    }
}
