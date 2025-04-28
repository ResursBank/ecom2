<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\CallbackList;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Module\Widget\CallbackList\Css;

/**
 * Tests for the CallbackList CSS widget.
 */
class CssTest extends TestCase
{
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
