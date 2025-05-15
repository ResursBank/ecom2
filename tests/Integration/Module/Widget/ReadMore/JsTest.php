<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\ReadMore;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Module\Widget\ReadMore\Js;

/**
 * Tests ReadMore JS widget rendering.
 */
class JsTest extends TestCase
{
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
