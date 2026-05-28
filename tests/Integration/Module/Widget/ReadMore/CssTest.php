<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\ReadMore;

use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\ReadMore\Css;

/**
 * Tests for the Read More CSS widget.
 */
#[AllowMockObjectsWithoutExpectations]
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
                type: LoggerInterface::class
            ),
            cache: $this->createMock(type: CacheInterface::class),
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
     * Verify that widget content has rendered.
     */
    public function testContent(): void
    {
        $widget = new Css();
        $this->assertNotEmpty(actual: $widget->content);

        $definitions = [
            '.rb-rm-link div',
            '.rb-rm-background',
            '.rb-rm-iframe-container',
            '.rb-rm-iframe',
            '.rb-rm-close'
        ];

        foreach ($definitions as $definition) {
            $this->assertStringContainsString(
                needle: $definition,
                haystack: $widget->content
            );
        }
    }
}
