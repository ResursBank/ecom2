<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\GetAddress;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\GetAddress\Js;

/**
 * Integration tests for the GetAddress JS widget.
 */
#[AllowMockObjectsWithoutExpectations]
class JsTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/customer/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        parent::setUp();
    }

    /**
     * Configure with Finnish store ID.
     *
     * @throws EmptyValueException
     */
    private function configureFi(): void
    {
        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/customer/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID_FI']
        );
    }

    /**
     * Confirm the following:
     *
     * $data->content does not contain:
     *
     * - "new Resursbank_GetAddress().setupEventListeners();" to ensure
     * $this->>automatic is respected.
     */
    public function testRenderMin(): void
    {
        $data = new Js(url: 'https://example.com/');

        // Confirm automatic is respected.
        static::assertDoesNotMatchRegularExpression(
            pattern: '/new Resursbank_GetAddress\(\).setupEventListeners\(\);/',
            string: $data->content,
            message: 'Get address widget should not contain "new Resursbank_GetAddress().setupEventListeners();".'
        );
    }

    /**
     * Confirm the following:
     *
     * $data->content:
     *
     * - Contains "return 'https://example.com'" to confirm URL is used.
     * - Contains "new Resursbank_GetAddress().setupEventListeners();" to ensure
     * $this->>automatic is respected.
     */
    public function testRenderMax(): void
    {
        $data = new Js(url: 'https://example.com/', automatic: true);

        // Confirm URL is used.
        static::assertStringContainsString(
            needle: 'return \'https://example.com/\'',
            haystack: $data->content,
            message: 'Get address widget should contain URL "https://example.com".'
        );

        // Confirm automatic is respected.
        static::assertStringContainsString(
            needle: 'new Resursbank_GetAddress().setupEventListeners();',
            haystack: $data->content,
            message: 'Get address widget should contain "new Resursbank_GetAddress().setupEventListeners();".'
        );
    }

    /**
     * Verify that shouldRender renders true for SE and false for others.
     *
     * @throws EmptyValueException
     * @throws ConfigException
     */
    public function testShouldRender(): void
    {
        $widget = new Js(url: 'https://example.com/');

        static::assertTrue(
            condition: $widget->shouldRender()
        );

        $this->configureFi();

        static::assertFalse(
            condition: $widget->shouldRender()
        );
    }
}
