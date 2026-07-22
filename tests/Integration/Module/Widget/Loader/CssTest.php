<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\Loader;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Module\Widget\Loader\Css;

/**
 * Test creation of Loader CSS.
 */
class CssTest extends TestCase
{
    /**
     * Test instance creation.
     */
    public function testCreate(): void
    {
        Config::setup();
        $widget = new Css();

        $this->assertNotEmpty(actual: $widget->content);
    }
}
