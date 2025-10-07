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
use Resursbank\Ecom\Module\Widget\CostList\Css;

/**
 * Tests for the CostList CSS widget.
 */
class CssTest extends TestCase
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
     * Verify that rendered content isn't empty.
     */
    public function testContentNotEmpty(): void
    {
        $widget = new Css();

        $this->assertNotEmpty(actual: $widget->content);
    }

    /**
     * Verify that known CSS selectors used in template are present.
     */
    public function testContentContainsSelectors(): void
    {
        $widget = new Css();

        $knownSelectors = [
            '.rb-ps-cl',
            '.rb-ps-cl-expander',
            '.rb-ps-cl-expander-icon',
            '.rb-ps-cl.expanded .rb-ps-cl-expander-icon',
            '.rb-ps-cl-content',
            '.rb-ps-cl.expanded .rb-ps-cl-content',
            '.rb-ps-cl-info',
            '.rb-ps-cl-title',
            '.rb-ps-cl-bold',
            '.rb-ps-cl-row-wrapper',
            '.rb-ps-cl-row-wrapper.expanded',
            '.rb-ps-cl-row',
            '.rb-ps-cl-row p',
            '.rb-ps-cl-row.rb-ps-cl-title p:first-child',
            '.rb-ps-cl-row.rb-ps-cl-title p:last-child',
            '.rb-ps-cl-section',
            '.rb-ps-cl-section.expanded',
            '.rb-ps-cl-section:not(.expanded)',
            '.rb-ps-cl-section:not(:first-child)',
            '.rb-ps-cl-section:first-child',
            '.rb-ps-cl-section.expanded:not(:first-child)',
            '.rb-ps-cl-section.expanded + .rb-ps-cl-section'
        ];

        foreach ($knownSelectors as $selector) {
            $this->assertStringContainsString(
                needle: $selector,
                haystack: $widget->content
            );
        }
    }
}
