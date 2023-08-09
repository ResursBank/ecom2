<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\UrlValidationException;
use Resursbank\Ecom\Lib\Model\Rco\Redirects;

/**
 * Special tests for the Redirects class in RCO+.
 */
class RedirectsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Validation of proper urls.
     */
    public function testRedirectsGoodUrl(): void
    {
        $this->assertInstanceOf(
            expected: Redirects::class,
            actual: new Redirects(checkout: 'https://www.example.com')
        );
    }

    public function testRedirectsEmptySuccess(): void
    {
        $this->assertInstanceOf(
            expected: Redirects::class,
            actual: new Redirects(
                checkout: 'https://www.example.com',
                success: ''
            )
        );
    }
}
