<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethodElements;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\CustomerType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentMethodElements\Repository;

/**
 * Tests for Payment Method Elements repository class.
 */
class RepositoryTest extends TestCase
{
    /**
     * Verify basic functioning of getSession.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws NotJsonEncodedException
     */
    public function testGetSession(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::CREDENTIALS
            ),
            storeId: $_ENV['RWS_STORE_ID']
        );
        $identifier = Strings::generateRandomString(
            length: 16,
            characters: '0123456789'
        );

        $session = Repository::getSession(identifier: $identifier);

        $this->assertNotEmpty(actual: $session->id);
    }

    /**
     * Verify basic functioning of getPaymentMethodGroups
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testGetPaymentMethodGroups(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::CREDENTIALS
            ),
            storeId: $_ENV['RWS_STORE_ID']
        );

        $identifier = Strings::generateRandomString(
            length: 16,
            characters: '0123456789'
        );
        $session = Repository::getSession(identifier: $identifier);

        $result = Repository::getPaymentMethodGroups(
            amount: 1000.00,
            locale: 'sv-SE',
            sessionId: $session->id
        );

        $this->assertNotEmpty(actual: $result);

        $result = Repository::getPaymentMethodGroups(
            amount: 1000.00,
            locale: 'sv-SE',
            sessionId: $session->id,
            customerType: CustomerType::NATURAL
        );

        $this->assertNotEmpty(actual: $result);
    }
}
