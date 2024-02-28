<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection DuplicatedCode */

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
    }
}
