<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethodElements;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentMethodElements\Repository;

// We must start the session before PHPUnit runs, otherwise sessions won't work
// and we cannot test the session storage functionality.
/** @phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols */
session_start();

/**
 * Integration tests for Payment Method Elements repository class.
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

        parent::setUp();
    }
}
