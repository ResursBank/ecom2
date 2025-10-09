<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\CostList;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\CostList\Js;

/**
 * Tests for the CostList JS widget.
 */
class JsTest extends TestCase
{
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
     * Verify that the widget renders.
     */
    public function testContentRendering(): void
    {
        $widget = new Js(containerElDomPath: 'foobar');

        $this->assertNotEmpty(actual: $widget->content);
        $this->assertStringContainsString(
            needle: 'class Resursbank_CostList',
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "document.querySelector('" . $widget->containerElDomPath .
            "')",
            haystack: $widget->content
        );
    }

    /**
     * Verify that the widget renders properly with auto-init disabled.
     */
    public function testWithoutAutoRendering(): void
    {
        $widget = new Js(containerElDomPath: 'foobar', auto: false);

        $this->assertNotEmpty(actual: $widget->content);
        $this->assertStringContainsString(
            needle: 'class Resursbank_CostList',
            haystack: $widget->content
        );
        $this->assertStringNotContainsString(
            needle: "document.addEventListener('DOMContentLoaded', () => {",
            haystack: $widget->content
        );
        $this->assertStringNotContainsString(
            needle: "document.querySelector('" . $widget->containerElDomPath .
            "')",
            haystack: $widget->content
        );
    }
}
