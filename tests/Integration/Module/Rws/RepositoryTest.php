<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rws;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Rws\Repository;

// We must start the session before PHPUnit runs, otherwise sessions won't work
// and we cannot test the session storage functionality.
/** @phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols */
session_start();

/**
 * Integration tests for RWS repository class.
 */
class RepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['RWS_STORE_ID']
        );

        Config::getSessionHandler()->init();
        Config::getSessionHandler()->delete(
            key: Repository::SESSION_TOKEN_CACHE_KEY
        );

        parent::setUp();
    }

    /**
     * Verify getSessionToken behaves correctly.
     *
     * Assert that we can resolve an RWS session token from the API, this token
     * is stored in our local PHP session, and is later resolved from this
     * session instead of a new one being fetched from the API.
     */
    public function testGetSessionToken(): void
    {
        // Confirm no pre-existing token data in session.
        $data = Config::getSessionHandler()->get(
            key: Repository::SESSION_TOKEN_CACHE_KEY
        );
        $this->assertEquals(expected: null, actual: $data);

        // Resolve new session token from API.
        $token = Repository::getSessionToken();
        $this->assertNotEmpty(actual: $token->token);

        // Confirm token is kept in session, resolve and examine data directly from
        // session to ensure no middleware interference can cause false positives.
        $data = json_decode(
            json: (string) Config::getSessionHandler()->get(
                key: Repository::SESSION_TOKEN_CACHE_KEY
            ),
            flags: JSON_THROW_ON_ERROR,
            associative: true
        );

        $this->assertIsArray(actual: $data);
        $this->assertArrayHasKey(key: 'token', array: $data);
        $this->assertArrayHasKey(key: 'expiresAt', array: $data);
        $this->assertNotEmpty(actual: $data['token']);
        $this->assertNotEmpty(actual: $data['expiresAt']);
        $this->assertEquals(expected: $token->token, actual: $data['token']);
        $this->assertEquals(
            expected: $token->expiresAt,
            actual: $data['expiresAt']
        );

        // Confirm that, fetching session token using the Repository, will
        // resolve the same key again.
        $this->assertEquals(
            expected: $token,
            actual: Repository::getSessionToken()
        );
    }
}
