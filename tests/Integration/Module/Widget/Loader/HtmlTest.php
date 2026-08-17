<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\Loader;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Module\Widget\Loader\Html;

/**
 * Test creation of Loader HTML.
 */
class HtmlTest extends TestCase
{
    /**
     * Test instance creation.
     */
    public function testCreate(): void
    {
        Config::setup();
        $widget = new Html();

        $this->assertNotEmpty(actual: $widget->content);
    }
}
