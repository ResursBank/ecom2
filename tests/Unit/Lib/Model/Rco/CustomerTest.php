<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Model\Rco\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;

/**
 * Unit tests for Lib\Model\Rco\Customer
 */
class CustomerTest extends TestCase
{
    /**
     * Assert that correct type of exception is thrown for malformed government ID.
     */
    public function testInvalidGovernmentId(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        new Customer(type: Customer\Type::B2C, governmentId: 'SO12345678');
    }

    /**
     * Assert that a valid-looking government ID is stored unmodified in the object.
     */
    public function testValidGovernmentId(): void
    {
        $id = 'SE810101-1001';
        $customer = new Customer(type: Customer\Type::B2C, governmentId: $id);

        $this->assertEquals(expected: $id, actual: $customer->governmentId);
    }

    /**
     * Assert the useSeparateDeliveryAddress() method returns true only when
     * the delivery property is not null.
     */
    public function testUseSeparateDeliveryAddress(): void
    {
        $same = new Customer(
            type: Customer\Type::B2C,
            governmentId: 'SE810101-1001'
        );

        $same2 = new Customer(
            type: Customer\Type::B2C,
            governmentId: 'SE810101-1001',
            billing: new Recipient(
                name: 'Kermit'
            )
        );

        $notSame = new Customer(
            type: Customer\Type::B2C,
            governmentId: 'SE810101-1001',
            billing: new Recipient(
                name: 'Kermit'
            ),
            delivery: new Recipient(
                name: 'Chef'
            )
        );

        $this->assertFalse(condition: $same->useSeparateDeliveryAddress());
        $this->assertFalse(condition: $same2->useSeparateDeliveryAddress());
        $this->assertTrue(condition: $notSame->useSeparateDeliveryAddress());
    }
}
