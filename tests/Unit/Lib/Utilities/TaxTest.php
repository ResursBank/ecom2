<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Utilities\Tax;

/**
 * Test for Tax utility class.
 */
class TaxTest extends TestCase
{
    /**
     * Verify that getRate works as intended.
     */
    public function testGetRate(): void
    {
        $this->assertEquals(
            expected: 0.0,
            actual: Tax::getRate(
                taxAmount: 0.0,
                totalInclTax: 0.0
            )
        );

        $this->assertEquals(
            expected: 0.0,
            actual: Tax::getRate(
                taxAmount: 10.0,
                totalInclTax: 0.0
            )
        );

        $this->assertEquals(
            expected: 0.0,
            actual: Tax::getRate(
                taxAmount: 0.0,
                totalInclTax: 10.0
            )
        );

        $values = [
            30 => 200,
            25 => 125,
            40 => 500
        ];

        foreach ($values as $taxAmount => $totalInclTax) {
            $actual = Tax::getRate($taxAmount, $totalInclTax);

            $this->assertEquals(
                expected: round(
                    num: $taxAmount / $totalInclTax,
                    precision: 2
                ) * 100,
                actual: $actual
            );
        }
    }
}
