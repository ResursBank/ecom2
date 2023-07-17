<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\UrlValidationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Merchant;

/**
 * Special merchant class validation tests (for RCO+).
 */
class MerchantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testBadDisplayName(): void
    {
        $this->expectException(EmptyValueException::class);
        new Merchant(displayName: '', logoUrl: '', homepageUrl: '');
    }

    public function testBadUrls(): void
    {
        $this->expectException(exception: UrlValidationException::class);
        new Merchant(
            displayName: 'DisplayName',
            logoUrl: 'bad-url',
            homepageUrl: 'bad-url'
        );
    }

    /**
     * Validation of proper urls.
     */
    public function testMerchantGoodUrl(): void
    {
        $this->assertInstanceOf(
            expected: Merchant::class,
            actual: new Merchant(
                displayName: 'DisplayName',
                logoUrl: '',
                homepageUrl: ''
            )
        );
    }
}
