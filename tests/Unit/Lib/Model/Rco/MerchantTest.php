<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Merchant;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Lib\Model\Rco\Merchant
 */
class MerchantTest extends TestCase
{
    /**
     * Verify that a too short displayName results in an exception being thrown.
     *
     * @throws Exception
     */
    public function testTooShortDisplayName(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Merchant(
            displayName: Strings::generateRandomString(length: 1)
        );
    }

    /**
     * Verify that a too short displayName results in an exception being thrown.
     *
     * @throws Exception
     */
    public function testTooLongDisplayName(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        new Merchant(
            displayName: Strings::generateRandomString(length: 129)
        );
    }

    /**
     * Verify that valid displayName lengths are accepted without errors.
     *
     * @throws Exception
     */
    public function testValidDisplayNames(): void
    {
        $min = Strings::generateRandomString(length: 2);
        $max = Strings::generateRandomString(length: 128);
        $minMerchant = new Merchant(displayName: $min);
        $maxMerchant = new Merchant(displayName: $max);

        $this->assertEquals(expected: $min, actual: $minMerchant->displayName);
        $this->assertEquals(expected: $max, actual: $maxMerchant->displayName);
    }

    /**
     * Verify that an invalid logoUrl results in an exception being thrown.
     *
     * @throws Exception
     */
    public function testInvalidLogoUrl(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new Merchant(
            displayName: Strings::generateRandomString(length: 12),
            logoUrl: 'foobar'
        );
    }

    /**
     * Verify that a valid logoUrl is accepted without errors.
     *
     * @throws Exception
     */
    public function testValidLogoUrl(): void
    {
        $url = 'https://example.com';
        $merchant = new Merchant(
            displayName: Strings::generateRandomString(length: 12),
            logoUrl: $url
        );
        $this->assertEquals(expected: $url, actual: $merchant->logoUrl);
    }

    /**
     * Verify that an invalid homepageUrl results in an exception being thrown.
     *
     * @throws Exception
     */
    public function testInvalidHomepageUrl(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new Merchant(
            displayName: Strings::generateRandomString(length: 12),
            homepageUrl: 'foobar'
        );
    }

    /**
     * Verify that a valid homepageUrl is accepted without errors.
     *
     * @throws Exception
     */
    public function testValidHomepageUrl(): void
    {
        $url = 'https://example.com';
        $merchant = new Merchant(
            displayName: Strings::generateRandomString(length: 12),
            homepageUrl: $url
        );
        $this->assertEquals(expected: $url, actual: $merchant->homepageUrl);
    }
}
