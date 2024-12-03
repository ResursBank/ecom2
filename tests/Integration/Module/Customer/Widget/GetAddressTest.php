<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Customer\Widget;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Customer\Widget\GetAddress;

/**
 * Integration tests for the GetAddress widget.
 */
class GetAddressTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
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
                originalClassName: LoggerInterface::class
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
     * $data->content contains:
     *
     * - Element with id "rb-ga-widget"
     * - Input with id "rb-ga-ct-natural" has "checked" attribute
     * - Input with id rb-ga-ct-legal does not have "checked" attribute
     * - Input with id rb-ga-gov-id has empty value
     * - Element with id rb-ga-error has no innerHtml
     *
     * $data->js does not contain:
     *
     * - "new Resursbank_GetAddress().setupEventListeners();" to ensure
     * $this->>automatic is respected.
     */
    public function testRenderMin(): void
    {
        $data = new GetAddress();

        static::assertStringContainsString(
            needle: 'id="rb-ga-widget"',
            haystack: $data->content,
            message: 'Get address widget should contain an element with id "rb-ga-widget".'
        );

        // Check input element with id "rb-ga-ct-natural" exists and that
        // "checked" comes before the end of the tag.
        static::assertMatchesRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-ga-ct-natural[^>]+checked[^>]*>/',
            string: $data->content,
            message: 'Get address widget should contain an input with id "rb-ga-ct-natural" and "checked" attribute.'
        );

        // Confirm legal input does not have "checked" attribute.
        static::assertDoesNotMatchRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-ga-ct-legal[^>]+checked[^>]*>/',
            string: $data->content,
            message: 'Get address widget should contain an input with id "rb-ga-ct-legal" and no "checked" attribute.'
        );

        // Confirm gov id input has empty value.
        static::assertMatchesRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-ga-gov-id[^>]+value=["\'][^"\']*["\'][^>]*>/',
            string: $data->content,
            message: 'Get address widget should contain an input with id "rb-ga-gov-id" and empty value.'
        );

        // Confirm error element has no innerHtml.
        static::assertMatchesRegularExpression(
            pattern: '/<div[^>]+id=["\'][^"\']*rb-ga-error[^>]*>\s*<\/div>/',
            string: $data->content,
            message: 'Get address widget should contain an element with id "rb-ga-error" and no innerHtml.'
        );

        // Confirm automatic is respected.
        static::assertDoesNotMatchRegularExpression(
            pattern: '/new Resursbank_GetAddress\(\).setupEventListeners\(\);/',
            string: $data->js,
            message: 'Get address widget should not contain "new Resursbank_GetAddress().setupEventListeners();".'
        );
    }

    /**
     * Confirm the following:
     *
     * $data->content contains:
     *
     * - Element with id "rb-ga-widget"
     * - Input with id "rb-ga-ct-legal" has "checked" attribute
     * - Input with id "rb-ga-ct-natural" does not have "checked" attribute
     * - Input with id "rb-ga-gov-id" has value "1234567890"
     * - Element with id "rb-ga-error" has empty innerHtml
     *
     * $data->js containers:
     *
     * - contains "return 'https://example.com'" to confirm URL is used.
     * - contains "new Resursbank_GetAddress().setupEventListeners();" to ensure
     * $this->>automatic is respected.
     */
    public function testRenderMax(): void
    {
        $data = new GetAddress(
            url: 'https://example.com',
            govId: '1234567890',
            customerType: CustomerType::LEGAL,
            automatic: true
        );

        static::assertStringContainsString(
            needle: 'id="rb-ga-widget"',
            haystack: $data->content,
            message: 'Get address widget should contain an element with id "rb-ga-widget".'
        );

        // Check input element with id "rb-ga-ct-legal" exists and that
        // "checked" comes before the end of the tag.
        static::assertMatchesRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-ga-ct-legal[^>]+checked[^>]*>/',
            string: $data->content,
            message: 'Get address widget should contain an input with id "rb-ga-ct-legal" and "checked" attribute.'
        );

        // Confirm natural input does not have "checked" attribute.
        static::assertDoesNotMatchRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-ga-ct-natural[^>]+checked[^>]*>/',
            string: $data->content,
            message: 'Get address widget should contain an input with id "rb-ga-ct-natural" and no "checked" attribute.'
        );

        // Confirm gov id input has value "1234567890".
        static::assertMatchesRegularExpression(
            pattern: '/<input[^>]+id=["\'][^"\']*rb-ga-gov-id[^>]+value=["\']1234567890["\'][^>]*>/',
            string: $data->content,
            message: 'Get address widget should contain an input with id "rb-ga-gov-id" and value "1234567890".'
        );

        // Confirm error element has no innerHtml.
        static::assertMatchesRegularExpression(
            pattern: '/<div[^>]+id=["\'][^"\']*rb-ga-error[^>]*>\s*<\/div>/',
            string: $data->content,
            message: 'Get address widget should contain an element with id "rb-ga-error" and no innerHtml.'
        );

        // Confirm URL is used.
        static::assertStringContainsString(
            needle: 'return \'https://example.com\'',
            haystack: $data->js,
            message: 'Get address widget should contain URL "https://example.com".'
        );

        // Confirm automatic is respected.
        static::assertStringContainsString(
            needle: 'new Resursbank_GetAddress().setupEventListeners();',
            haystack: $data->js,
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
        $widget = new GetAddress();
        static::assertTrue(
            condition: $widget->shouldRender()
        );

        $this->configureFi();

        static::assertFalse(
            condition: $widget->shouldRender()
        );
    }
}
