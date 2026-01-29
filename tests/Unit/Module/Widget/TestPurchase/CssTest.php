<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\TestPurchase;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Module\Widget\TestPurchase\Css;

/**
 * Unit tests for the TestPurchase Css widget.
 */
class CssTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Config::setup();
    }

    /**
     * Verify that all listed selectors exist in rendered widget.
     */
    public function testRender(): void
    {
        $widget = new Css();

        $selectors = [
            '#rb-tp-result',
            '#rb-tp-result table',
            '#rb-tp-result table tr:nth-child(even)',
            '#rb-tp-result table tr:nth-child(odd)',
            '#rb-tp-result table tr:last-child',
            '#rb-tp-result table tr td',
            '#rb-tp-result table tr th',
            '#rb-tp-result #rb-tp-result-messages',
            '#rb-tp-trigger',
            '#rb-tp-trigger:hover'
        ];

        foreach ($selectors as $selector) {
            $this->assertStringContainsString(
                needle: $selector,
                haystack: $widget->content
            );
        }
    }
}
