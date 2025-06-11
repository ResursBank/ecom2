<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\SupportInfo;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Module\Widget\SupportInfo\Css;

/**
 * Tests for SupportInfo CSS widget.
 */
class CssTest extends TestCase
{
    /**
     * Verify that the CSS is rendered.
     */
    public function testRenderCss(): void
    {
        $widget = new Css();
        $this->assertNotEmpty(
            actual: $widget->content,
            message: 'Support Info widget CSS is empty'
        );

        $this->assertStringContainsString(
            needle: '.rb-si',
            haystack: $widget->content,
            message: 'Support Info widget CSS is missing the top element'
        );
    }
}
