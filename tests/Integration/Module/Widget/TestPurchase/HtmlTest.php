<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\TestPurchase;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\TestPurchase\Html;

/**
 * Unit tests for the TestPurchase HTML widget.
 */
class HtmlTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );
    }

    /**
     * Verify that the widget constructor renders its contents.
     */
    public function testRender(): void
    {
        $widget = new Html();

        $this->assertStringContainsString(
            needle: 'rb-tp-trigger',
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: 'rb-tp-result',
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: 'rb-tp-result-messages',
            haystack: $widget->content
        );
    }
}
