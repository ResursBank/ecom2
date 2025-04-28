<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Widget;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Test various widget methods.
 */
final class WidgetTest extends TestCase
{
    public function testGetTagNames(): void
    {
        $this->assertSame(
            expected: ['html', 'body', 'span', 'a', 'div'],
            actual: Widget::getTagNames(content:
                '<html><body><span><a href="testing">Testing</a><div class="famous">People walking</div></span></body>')
        );

        // Assert empty content generates empty array.
        $this->assertSame(
            expected: [],
            actual: Widget::getTagNames(content: '')
        );
    }

    public function testExceptionDuringRendering(): void
    {
        $widget = new Widget();
        $result = $widget->render(file: __DIR__ . 'fsdjiosefhuivnsjk');
        $this->assertEquals(expected: '', actual: $result);
    }

    /**
     * Verify that an empty string is returned of file doesn't exist.
     */
    public function testRenderStatic(): void
    {
        $widget = new Widget();
        $result = $widget->renderStatic(file: __DIR__ . 'fsdjiosefhuivnsjk');
        $this->assertEquals(expected: '', actual: $result);
    }
}
