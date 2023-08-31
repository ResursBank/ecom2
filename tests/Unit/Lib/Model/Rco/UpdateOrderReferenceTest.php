<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Model\Rco\UpdateOrderReference;

/**
 * Unit tests for Model\Rco\UpdateOrderReference
 */
class UpdateOrderReferenceTest extends TestCase
{
    /**
     * Verify that an invalid value for orderReference causes an exception to be thrown.
     */
    public function testInvalidOrderReference(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new UpdateOrderReference(orderReference: 'abc_123');
    }

    /**
     * Verify that valid orderReference values are stored unaltered.
     */
    public function testValidOrderReference(): void
    {
        $minReference = 'a';
        $maxReference = 'abcdefghijklmnopqrstuvwxyzabcdef';
        $minUpdate = new UpdateOrderReference(orderReference: $minReference);
        $maxUpdate = new UpdateOrderReference(orderReference: $maxReference);

        $this->assertEquals(
            expected: $minReference,
            actual: $minUpdate->orderReference
        );
        $this->assertEquals(
            expected: $maxReference,
            actual: $maxUpdate->orderReference
        );
    }
}
