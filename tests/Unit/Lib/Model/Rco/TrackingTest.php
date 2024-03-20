<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Model\Rco\Tracking;

/**
 * Unit tests for Tracking.
 */
class TrackingTest extends TestCase
{
    /**
     * Assert that a valid URL doesn't cause an exception to be thrown.
     */
    public function testValidUrl(): void
    {
        $url = 'https://www.example.com';
        $tracking = new Tracking(url: $url);

        $this->assertEquals(expected: $url, actual: $tracking->url);
    }

    /**
     * Assert that an invalid URL throws an IllegalCharsetException.
     */
    public function testInvalidUrl(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new Tracking(url: 'hppt://www.example.com');
    }
}
