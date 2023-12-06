<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco\CreateCheckout;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateRedirects;

/**
 * Unit tests for Lib\Model\Rco\Redirects.
 */
class CreateRedirectsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Verify that no exceptions are thrown for valid URLs.
     */
    public function testValidUrls(): void
    {
        $this->assertInstanceOf(
            expected: CreateRedirects::class,
            actual: new CreateRedirects(
                checkout: 'https://www.example.com/{checkoutId}',
                success: 'https://www.example.com/{checkoutId}',
                failure: 'https://www.example.com/{checkoutId}',
                cancel: 'https://www.example.com/{checkoutId}'
            )
        );
    }

    /**
     * Verify that invalid checkout URL triggers an exception.
     */
    public function testInvalidCheckout(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new CreateRedirects(checkout: 'foobar');
    }

    /**
     * Verify that invalid success URL triggers an exception.
     */
    public function testInvalidSuccess(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new CreateRedirects(success: 'foobar');
    }

    /**
     * Verify that invalid failure URL triggers an exception.
     */
    public function testInvalidFailure(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new CreateRedirects(failure: 'foobar');
    }

    /**
     * Verify that invalid cancel URL triggers an exception.
     */
    public function testInvalidCancel(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new CreateRedirects(cancel: 'foobar');
    }
}
