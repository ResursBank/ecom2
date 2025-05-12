<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PartPayment;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Module\Widget\PartPayment\Css;

/**
 * Integration test for the Part payment CSS widget
 */
class CssTest extends TestCase
{
    /**
     * Confirm widget CSS content is rendered as expected.
     */
    public function testWidgetCss(): void
    {
        $widget = new Css();

        // Confirm CSS content is rendered.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp/',
            string: $widget->content,
            message: 'Widget should contain CSS content.'
        );

        // Confirm CSS content contains rb-pp-info.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-info/',
            string: $widget->content,
            message: 'Widget CSS should contain rb-pp-info.'
        );

        // Confirm CSS content contains rb-pp-starting-at.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-starting-at/',
            string: $widget->content,
            message: 'Widget CSS should contain rb-pp-starting-at.'
        );

        // Confirm CSS content contains rb-pp-error.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-error/',
            string: $widget->content,
            message: 'Widget CSS should contain rb-pp-error.'
        );

        // Confirm CSS content contains rb-pp-overlay.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-overlay/',
            string: $widget->content,
            message: 'Widget CSS should contain rb-pp-overlay.'
        );

        // Confirm CSS content contains rb-pp-loader.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-loader/',
            string: $widget->content,
            message: 'Widget CSS should contain rb-pp-loader.'
        );

        // Confirm CSS content contains rb-pp-spinner.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-spinner/',
            string: $widget->content,
            message: 'Widget CSS should contain rb-pp-spinner.'
        );
    }
}
