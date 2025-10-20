<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\CallbackList;

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
use Resursbank\Ecom\Module\Widget\CallbackList\Css;

/**
 * Tests for the CallbackList CSS widget.
 */
class CssTest extends TestCase
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
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
     * Assert that rendered content contains data from the template.
     */
    public function testRenderedContent(): void
    {
        $widget = new Css();

        $this->assertNotEmpty(actual: $widget->content);

        $this->assertStringContainsString(
            needle: '.rb-cb-header',
            haystack: $widget->content
        );

        $this->assertStringContainsString(
            needle: '.rb-cb-url',
            haystack: $widget->content
        );
    }
}
