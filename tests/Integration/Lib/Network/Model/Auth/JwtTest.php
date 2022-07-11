<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network\Model\Auth;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
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
     */
    public function testGetToken(): void
    {
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
    }
}
