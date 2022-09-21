<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Payment\Enum\Status;
use DateTime;

/**
 * Tests for the Resursbank\Ecom\Lib\Model\Payment class
 */
class PaymentTest extends TestCase
{
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
        return vsprintf(format: '%s%s-%s-%s-%s-%s%s%s', values: str_split(string: bin2hex($data), length: 4));
    }

    /**
     * Create a dummy Payment object with the specified possible actions
     *
     * @param Status $status
     * @return Payment
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    private function createDummyPayment(Status $status): Payment
    {
        return new Payment(
            id: $this->generateUuid(),
            created: (new DateTime())->format(format: 'c'),
            storeId: $this->generateUuid(),
            paymentMethodId: $this->generateUuid(),
            customer: new Payment\Customer(
                customerType: CustomerType::NATURAL
            ),
            status: $status,
            paymentActions: [],
            order: new Payment\Order(
                orderReference: $this->generateUuid(),
                actionLog: new Payment\Order\ActionLogCollection(data: []),
                possibleActions: new Payment\Order\PossibleActionCollection(data: []),
                totalOrderAmount: 100.00,
                canceledAmount: 0.00,
                authorizedAmount: 100.00,
                capturedAmount: 0.00,
                refundedAmount: 0.00
            )
        );
    }

    /**
     * Verify that the isFrozen method works as intended
     *
     * @return void
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsFrozen(): void
    {
        $isFrozen = $this->createDummyPayment(status: Status::FROZEN);
        $notFrozen = $this->createDummyPayment(status: Status::ACCEPTED);

        self::assertEquals(
            expected: true,
            actual: $isFrozen->isFrozen()
        );
        self::assertEquals(
            expected: false,
            actual: $notFrozen->isFrozen()
        );
    }
}
