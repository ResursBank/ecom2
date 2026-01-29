<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\TestPurchase;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Module\Widget\TestPurchase\Js;

/**
 * Unit tests for the TestPurchase JS widget.
 */
class JsTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Config::setup();
    }

    /**
     * Verify that widget is rendered with supplied controller URL.
     *
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function testRender(): void
    {
        $url = 'https://example.com/foo';
        $widget = new Js(controllerUrl: $url, countryCode: CountryCode::SE);

        $this->assertStringContainsString(
            needle: "const url = '" . $url . "';",
            haystack: $widget->content
        );
    }
}
