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
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\GetAddress\Html;

/**
 * Integration tests for the GetAddress HTML widget.
 */
#[AllowMockObjectsWithoutExpectations]
class HtmlTest extends TestCase
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
     * $data->content contains:
     *
     * - Element with id "rb-ga-widget"
     * - Input with id "rb-ga-ct-natural" has "checked" attribute
     * - Input with id rb-ga-ct-legal does not have "checked" attribute
     * - Input with id rb-ga-gov-id has empty value
     * - Element with id rb-ga-error has no innerHtml
     */
    public function testRenderMin(): void
    {
        $data = new Html();

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
     */
    public function testRenderMax(): void
    {
        $data = new Html(
            govId: '1234567890',
            customerType: CustomerType::LEGAL,
            inputClassList: 'foo bar',
            btnClassList: 'bar foo'
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

        static::assertStringContainsString(
            needle: 'class="foo bar"',
            haystack: $data->content
        );

        static::assertStringContainsString(
            needle: '<button type="button" id="rb-ga-btn" class="bar foo">',
            haystack: $data->content
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
        $widget = new Html();
        static::assertTrue(
            condition: $widget->shouldRender()
        );

        $this->configureFi();

        static::assertFalse(
            condition: $widget->shouldRender()
        );
    }
}
