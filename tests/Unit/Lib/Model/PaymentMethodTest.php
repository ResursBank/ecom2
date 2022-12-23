<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;

use function chr;
use function in_array;
use function ord;
use function random_bytes;

/**
 * Tests for PaymentMethod functionality
 */
class PaymentMethodTest extends TestCase
{
    /**
     * Assert that isPartPayment gives correct responses depending on the method's type
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsPartPayment(): void
    {
        $validCases = [
            Type::RESURS_REVOLVING_CREDIT,
            Type::RESURS_PART_PAYMENT,
        ];

        foreach (Type::cases() as $case) {
            $method = $this->generatePaymentMethodWithType(type: $case);

            if (in_array(needle: $case, haystack: $validCases, strict: true)) {
                $this->assertTrue(condition: $method->isPartPayment());
            } else {
                $this->assertFalse(condition: $method->isPartPayment());
            }
        }
    }

    /**
     * Generate a bogus UUID
     *
     * @throws Exception
     */
    private function generateUuid(): string
    {
        $data = random_bytes(length: 16);
        $data[6] = chr(codepoint: ord(character: $data[6]) & 0x0f | 0x40);
        $data[8] = chr(codepoint: ord(character: $data[8]) & 0x3f | 0x80);

        return vsprintf(
            format: '%s%s-%s-%s-%s-%s%s%s',
            values: str_split(string: bin2hex(string: $data), length: 4)
        );
    }

    /**
     * Generate a dummy payment method with specified type
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    private function generatePaymentMethodWithType(Type $type): PaymentMethod
    {
        return new PaymentMethod(
            id: $this->generateUuid(),
            name: $this->generateUuid(),
            type: $type,
            minPurchaseLimit: 1,
            maxPurchaseLimit: 1000,
            minApplicationLimit: 1,
            maxApplicationLimit: 1000,
            legalLinks: new PaymentMethod\LegalLinkCollection(data: []),
            enabledForLegalCustomer: false,
            enabledForNaturalCustomer: true,
            sortOrder: 1
        );
    }
}
