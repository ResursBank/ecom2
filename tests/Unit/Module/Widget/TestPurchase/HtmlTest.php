<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\TestPurchase;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
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
        Config::setup();
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
