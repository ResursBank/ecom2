<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\RejectedReason;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Enum\RejectedReasonCategory;
use Resursbank\Ecom\Module\Payment\Enum\Status;

/**
 * Tests for the Resursbank\Ecom\Lib\Model\Payment class.
 *
 * @todo Missing unit tests ECP-254
 */
class PaymentTest extends TestCase
{
    /**
     * Create a dummy Payment object with the specified status
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function createDummyPayment(Status $status, ?RejectedReasonCategory $rejectedReasonCategory = null): Payment
    {
        return new Payment(
            id: Strings::getUuid(),
            created: (new DateTime())->format(format: 'c'),
            storeId: Strings::getUuid(),
            customer: new Payment\Customer(
                customerType: CustomerType::NATURAL
            ),
            status: $status,
            rejectedReason: new RejectedReason(
                category: $rejectedReasonCategory
            ),
            paymentActions: [],
            paymentMethod: new Payment\PaymentMethod(name: 'Payment method'),
            order: new Payment\Order(
                orderReference: Strings::getUuid(),
                actionLog: new Payment\Order\ActionLogCollection(data: []),
                possibleActions: new Payment\Order\PossibleActionCollection(
                    data: []
                ),
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
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsFrozen(): void
    {
        $isFrozen = $this->createDummyPayment(status: Status::FROZEN);
        $notFrozen = $this->createDummyPayment(status: Status::ACCEPTED);

        $this->assertTrue(
            condition: $isFrozen->isFrozen()
        );
        $this->assertFalse(
            condition: $notFrozen->isFrozen()
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function testIsCreditDenied(): void
    {
        $isDenied = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::CREDIT_DENIED
        );
        $notDenied = $this->createDummyPayment(status: Status::ACCEPTED);

        $this->assertTrue(
            condition: $isDenied->isDenied()
        );
        $this->assertTrue(
            condition: $isDenied->isRejected()
        );
        $this->assertFalse(
            condition: $notDenied->isDenied()
        );
        $this->assertFalse(
            condition: $notDenied->isRejected()
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function testIsAborted(): void
    {
        $isAborted = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::ABORTED_BY_CUSTOMER
        );
        $isDenied = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::CREDIT_DENIED
        );
        $notAborted = $this->createDummyPayment(status: Status::ACCEPTED);

        $this->assertTrue(
            condition: $isDenied->isRejected()
        );
        $this->assertTrue(
            condition: $isAborted->isAbortedByCustomer()
        );
        $this->assertFalse(
            condition: $notAborted->isAbortedByCustomer()
        );
        $this->assertFalse(
            condition: $isDenied->isAbortedByCustomer()
        );
    }
}
