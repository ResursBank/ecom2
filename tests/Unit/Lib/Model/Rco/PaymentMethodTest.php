<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type as CustomerType;
use Resursbank\Ecom\Lib\Model\Rco\Customer\TypeCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\LinkCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

/**
 * Integrity test of RCO Checkout PaymentMethod model class.
 */
class PaymentMethodTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalTypeException
     */
    private function generateModel(): void
    {
        new PaymentMethod(
            methodId: '',
            name: '',
            type: Type::GENERIC,
            fee: 0,
            required: new RequiredCollection(data: []),
            subtitle: '',
            descriptions: [],
            terms: '',
            links: new LinkCollection(data: []),
            customerTypes: new TypeCollection(
                data: [CustomerType::B2C, CustomerType::B2B]
            ),
            minLimit: 10,
            maxLimit: 50000
        );
    }

    /**
     * Test generating a valid model instance.
     */
    public function testPaymentMethodModel(): void
    {
        try {
            $this->generateModel();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Failed to generate model instance.');
        }
    }

    /**
     * Check that invalid type in descriptions throws an exception.
     *
     * @throws IllegalTypeException
     * @throws Exception
     */
    public function testInvalidDescription(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        new PaymentMethod(
            methodId: Strings::generateRandomString(length: 12),
            name: Strings::generateRandomString(length: 12),
            type: Type::GENERIC,
            fee: 1000,
            required: new RequiredCollection(
                data: [Required::ADDRESS, Required::NAME->value]
            ),
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
            ]),
            customerTypes: new TypeCollection(
                data: [CustomerType::B2C, CustomerType::B2B]
            ),
            minLimit: 10,
            maxLimit: 50000
        );
    }
}
