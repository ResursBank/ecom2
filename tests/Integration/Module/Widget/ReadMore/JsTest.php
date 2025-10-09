<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\ReadMore;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\ReadMore\Js;

/**
 * Tests ReadMore JS widget rendering.
 */
class JsTest extends TestCase
{
    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::EN,
            storeId: $_ENV['STORE_ID']
        );

        parent::setUp();
    }

    /**
     * Verify that widget renders at all.
     */
    public function testContentNotEmpty(): void
    {
        $widget = new Js(containerElDomPath: 'foobar');

        $this->assertStringContainsString(
            needle: 'class Resursbank_ReadMore',
            haystack: $widget->content
        );
    }

    /**
     * Verify that the autoInitJs parameter works as intended.
     */
    public function testAutoInitJs(): void
    {
        // Auto init disabled
        $widget = new Js(containerElDomPath: 'foobar', autoInitJs: false);

        $this->assertStringNotContainsString(
            needle: "document.addEventListener('DOMContentLoaded', ()",
            haystack: $widget->content
        );

        // Auto init enabled
        $widget = new Js(containerElDomPath: 'foobar', autoInitJs: true);

        $this->assertStringContainsString(
            needle: "document.addEventListener('DOMContentLoaded', ()",
            haystack: $widget->content
        );
    }

    /**
     * Verify that containerElDomPath parameter is used correctly.
     */
    public function testContainerElDomPath(): void
    {
        $widget = new Js(containerElDomPath: 'foobar', autoInitJs: true);

        $this->assertStringContainsString(
            needle: 'document.querySelector(\'foobar\')',
            haystack: $widget->content
        );
    }
}
