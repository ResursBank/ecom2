<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\SupportInfo;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\SupportInfo\Html;

/**
 * Unit tests for the Support Info widget class.
 */
class HtmlTest extends TestCase
{
    private Html $widget;

    private string $pluginVersion = '1.4.4.7';

    /**
     * Initialize the environment.
     *
     * @throws EmptyValueException
     * @throws FilesystemException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::SV,
            storeId: $_ENV['STORE_ID']
        );

        $this->widget = new Html(
            minimumPhpVersion: '8.1',
            maximumPhpVersion: '8.3',
            pluginVersion: $this->pluginVersion
        );
    }

    /**
     * Assert getPhpVersion() method returns current PHP version.
     */
    public function testGetPhpVersion(): void
    {
        $this->assertEquals(
            expected: PHP_VERSION,
            actual: $this->widget->getPhpVersion(),
            message: 'PHP version does not match'
        );
    }

    /**
     * Confirm that getSslVersion() returns whatever is stored in constant
     * OPENSSL_VERSION_TEXT
     */
    public function testGetSslVersion(): void
    {
        $this->assertEquals(
            expected: OPENSSL_VERSION_TEXT,
            actual: $this->widget->getSslVersion(),
            message: 'SSL version does not match'
        );
    }

    /**
     * Confirm that getCurlVersion() returns the version of the cURL library.
     */
    public function testGetCurlVersion(): void
    {
        $this->assertNotEmpty(
            $this->widget->getCurlVersion(),
            'cURL version is empty'
        );
    }

    /**
     * Verify getEcomVersion() resolves a version like value.
     *
     * @throws ConfigException
     */
    public function testGetEcomVersion(): void
    {
        $this->assertMatchesRegularExpression(
            pattern: '/\d+\.\d+\.\d+/',
            string: $this->widget->getEcomVersion(),
            message: 'eCom version does not match'
        );
    }

    /**
     * Assert that the widget appears to render as intended.
     *
     * @throws ConfigException
     */
    public function testRenderWidget(): void
    {
        // Confirm top element rb-si is present.
        $this->assertStringContainsString(
            needle: '<div class="rb-si">',
            haystack: $this->widget->content,
            message: 'Support Info widget is missing the top element'
        );

        // Confirm table element containing plugin version is present.
        $this->assertStringContainsString(
            needle: '<td>' . $this->pluginVersion . '</td>',
            haystack: $this->widget->content,
            message: 'Support Info widget is missing the plugin version'
        );

        // Confirm table element containing PHP version is present.
        $this->assertMatchesRegularExpression(
            pattern: '/<td.*>\n\s*' . PHP_VERSION . '/',
            string: $this->widget->content,
            message: 'Support Info widget is missing the PHP version'
        );

        // Confirm table element containing eCom version is present.
        $this->assertStringContainsString(
            needle: '<td>' . $this->widget->getEcomVersion() . '</td>',
            haystack: $this->widget->content,
            message: 'Support Info widget is missing the eCom version'
        );

        // Confirm table element containing SSL version is present.
        $this->assertStringContainsString(
            needle: '<td>' . $this->widget->getSslVersion() . '</td>',
            haystack: $this->widget->content,
            message: 'Support Info widget is missing the SSL version'
        );

        // Confirm table element containing cURL version is present.
        $this->assertMatchesRegularExpression(
            pattern: '/<td.*\n\s*' . $this->widget->getCurlVersion() . '/',
            string: $this->widget->content,
            message: 'Support Info widget is missing the cURL version'
        );
    }
}
