<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\ReadMore;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Module\Widget\ReadMore\Css;

/**
 * Tests for the Read More CSS widget.
 */
class CssTest extends TestCase
{
    /**
     * Verify that widget content has rendered.
     */
    public function testContent(): void
    {
        $widget = new Css();
        $this->assertNotEmpty(actual: $widget->content);

        $definitions = [
            '.rb-rm-link div',
            '.rb-rm-background',
            '.rb-rm-iframe-container',
            '.rb-rm-iframe',
            '.rb-rm-close'
        ];

        foreach ($definitions as $definition) {
            $this->assertStringContainsString(
                needle: $definition,
                haystack: $widget->content
            );
        }
    }
}
