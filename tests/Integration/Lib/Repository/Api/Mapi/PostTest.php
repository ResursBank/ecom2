<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Repository\Api\Mapi;

use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Post;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

/**
 * Test for Post class.
 */
class PostTest extends TestCase
{
    /**
     * Verify that class constructor properly instantiates object.
     *
     * @throws JsonException
     * @throws Exception
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     */
    public function testConstruct(): void
    {
        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        $route = '/v2/payments/search';

        $object = new Post(
            model: Payment::class,
            route: $route,
            params: [
                'storeId' => $_ENV['STORE_ID'],
                'orderReference' => Strings::generateRandomString(
                    length: 10
                )
            ],
            extractProperty: 'content'
        );

        try {
            $object->call();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail();
        }
    }
}
