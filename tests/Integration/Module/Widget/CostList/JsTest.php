<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\CostList;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Module\Widget\CostList\Js;

/**
 * Tests for the CostList JS widget.
 */
class JsTest extends TestCase
{
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
