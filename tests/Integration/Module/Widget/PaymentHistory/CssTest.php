<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PaymentHistory;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Module\Widget\PaymentHistory\Css;

/**
 * Tests JS widget rendering.
 */
class CssTest extends TestCase
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
     * Verify that the rendered content contains known CSS classes.
     *
     * Does not test for all known classes and IDs, just enough to verify that
     * the file has been rendered.
     */
    public function testRender(): void
    {
        $widget = new Css();

        $this->assertStringContainsString(
            needle: '#rb-ph {',
            haystack: $widget->content
        );

        $this->assertStringContainsString(
            needle: '#rb-ph a:link, #rb-ph a:visited {',
            haystack: $widget->content
        );

        $this->assertStringContainsString(
            needle: '#rb-ph .close-window-link {',
            haystack: $widget->content
        );

        $this->assertStringContainsString(
            needle: '#rb-ph .close-window-link .close-symbol {',
            haystack: $widget->content
        );
    }
}
