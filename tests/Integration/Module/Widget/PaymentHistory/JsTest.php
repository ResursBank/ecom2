<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PaymentHistory;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Module\Widget\PaymentHistory\Js;

/**
 * Tests JS widget rendering.
 */
class JsTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Config::setup();
        parent::setUp();
    }

    /**
     * Verify that rendered widget contains required methods.
     */
    public function testRender(): void
    {
        $widget = new Js();

        $this->assertStringContainsString(
            needle: 'function showExtra(data)',
            haystack: $widget->content
        );

        $this->assertStringContainsString(
            needle: 'function showLogTable()',
            haystack: $widget->content
        );

        $this->assertStringContainsString(
            needle: 'function showWidget()',
            haystack: $widget->content
        );

        $this->assertStringContainsString(
            needle: 'function hideWidget()',
            haystack: $widget->content
        );
    }
}
