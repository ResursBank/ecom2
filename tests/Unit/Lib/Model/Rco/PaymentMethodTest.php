<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for PaymentMethod.
 */
class PaymentMethodTest extends TestCase
{
    /**
     * Check that invalid type in descriptions throws an exception.
     *
     * @throws IllegalTypeException
     */
    public function testInvalidDescription(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        new PaymentMethod(
            methodId: Strings::generateRandomString(length: 12),
            name: Strings::generateRandomString(length: 12),
            type: Type::GENERIC,
            fee: 1000,
            required: [],
            subtitle: Strings::generateRandomString(length: 12),
            descriptions: [
                Strings::generateRandomString(length: 12),
                1234
            ],
            terms: Strings::generateRandomString(length: 12),
            links: new PaymentMethod\LinkCollection(data: [
                new PaymentMethod\Link(
                    label: Strings::generateRandomString(length: 12),
                    url: 'https://example.com'
                )
            ])
        );
    }
}
