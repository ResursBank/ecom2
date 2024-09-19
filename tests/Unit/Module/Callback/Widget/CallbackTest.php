<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Callback\Widget;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Callback\Widget\Callback;

/**
 * Tests for the Callback widget.
 */
class CallbackTest extends TestCase
{
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
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );
    }

    /**
     * Assert that supplied parameters are present in the rendered widget.
     */
    public function testRenderedContent(): void
    {
        $authorizationUrl = 'https://example.com/authorization';
        $managementUrl = 'https://example.com/management';

        $widget = new Callback(
            authorizationUrl: $authorizationUrl,
            managementUrl: $managementUrl
        );

        $this->assertStringContainsString(
            needle: $authorizationUrl,
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: $managementUrl,
            haystack: $widget->content
        );
    }

    /**
     * Assert that error message is rendered if no URL is supplied.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testRenderedContentWithMissingUrl(): void
    {
        $authorizationUrl = Translator::translate(
            phraseId: 'failed-to-resolve-callback-url'
        );
        $managementUrl = 'https://example.com/management';

        $widget = new Callback(
            authorizationUrl: null,
            managementUrl: $managementUrl
        );

        $this->assertStringContainsString(
            needle: $authorizationUrl,
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: $managementUrl,
            haystack: $widget->content
        );
    }
}
