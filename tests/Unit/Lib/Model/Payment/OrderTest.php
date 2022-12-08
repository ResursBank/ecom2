<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Payment;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment;
use DateTime;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Payment\Enum\PossibleAction;
use Resursbank\Ecom\Module\Payment\Enum\Status;

use function chr;
use function ord;

/**
 * Tests for the Order class.
 */
class OrderTest extends TestCase
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
        return vsprintf(
            format: '%s%s-%s-%s-%s-%s%s%s',
            values: str_split(string: bin2hex(string: $data), length: 4)
        );
    }

    /**
     * Create a dummy Payment object with the specified possible actions
     *
     * @param Payment\Order\PossibleActionCollection $possibleActions
     * @return Payment
     * @throws Exception
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function createDummyPayment(Payment\Order\PossibleActionCollection $possibleActions): Payment
    {
        return new Payment(
            id: $this->generateUuid(),
            created: (new DateTime())->format(format: 'c'),
            storeId: $this->generateUuid(),
            customer: new Payment\Customer(
                customerType: CustomerType::NATURAL
            ),
            paymentMethod: new Payment\PaymentMethod(name: 'Payment method'),
            status: Status::ACCEPTED,
            paymentActions: [],
            order: new Payment\Order(
                orderReference: $this->generateUuid(),
                actionLog: new Payment\Order\ActionLogCollection(data: []),
                possibleActions: $possibleActions,
                totalOrderAmount: 100.00,
                canceledAmount: 0.00,
                authorizedAmount: 100.00,
                capturedAmount: 0.00,
                refundedAmount: 0.00
            )
        );
    }

    /**
     * Verify that the canCancel method works as intended
     *
     * @return void
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testCanCancel(): void
    {
        $cancelable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(data: [
                new Payment\Order\PossibleAction(action: PossibleAction::CANCEL)
            ])
        );
        $unCancelable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(data: [
                new Payment\Order\PossibleAction(action: PossibleAction::REFUND),
                new Payment\Order\PossibleAction(action: PossibleAction::PARTIAL_REFUND)
            ])
        );

        $this->assertEquals(
            expected: true,
            actual: $cancelable->canCancel()
        );
        $this->assertEquals(
            expected: false,
            actual: $unCancelable->canCancel()
        );
    }

    /**
     * Verify that the canCapture method works as intended
     *
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function testCanCapture(): void
    {
        $captureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(data: [
                new Payment\Order\PossibleAction(action: PossibleAction::CAPTURE)
            ])
        );
        $uncaptureable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(data: [
                new Payment\Order\PossibleAction(action: PossibleAction::REFUND)
            ])
        );

        $this->assertEquals(
            expected: true,
            actual: $captureable->canCapture()
        );
        $this->assertEquals(
            expected: false,
            actual: $uncaptureable->canCapture()
        );
    }

    /**
     * Verify that the canRefund method works as intended
     *
     * @return void
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testCanRefund(): void
    {
        $refundable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(data: [
                new Payment\Order\PossibleAction(action: PossibleAction::REFUND)
            ])
        );
        $nonRefundable = $this->createDummyPayment(
            possibleActions: new Payment\Order\PossibleActionCollection(data: [
                new Payment\Order\PossibleAction(action: PossibleAction::CANCEL),
                new Payment\Order\PossibleAction(action: PossibleAction::CAPTURE)
            ])
        );

        $this->assertEquals(
            expected: true,
            actual: $refundable->canRefund()
        );
        $this->assertEquals(
            expected: false,
            actual: $nonRefundable->canRefund()
        );
    }
}
