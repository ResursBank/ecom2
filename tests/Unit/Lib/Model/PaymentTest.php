<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use DateInterval;
use DateTime;
use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\RejectedReason;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Enum\PossibleAction;
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
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function createDummyPayment(
        Status $status,
        ?RejectedReasonCategory $rejectedReasonCategory = null,
        ?Payment\Order\PossibleActionCollection $possibleActions = null,
        ?string $created = null
    ): Payment {
        if ($possibleActions === null) {
            $possibleActions = new Payment\Order\PossibleActionCollection(
                data: []
            );
        }

        if ($created === null) {
            $created = (new DateTime())->format(format: 'c');
        }

        return new Payment(
            id: Strings::getUuid(),
            created: $created,
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
     * Fetches test data for the can-prefixed method test.
     */
    private function getCanMethodList(): array
    {
        return [
            'canCancel' => [
                'status' => Status::ACCEPTED,
                'possibleAction' => PossibleAction::CANCEL
            ],
            'canPartiallyCancel' => [
                'status' => Status::ACCEPTED,
                'possibleAction' => PossibleAction::PARTIAL_CANCEL
            ],
            'canCapture' => [
                'status' => Status::ACCEPTED,
                'possibleAction' => PossibleAction::CAPTURE
            ],
            'canPartiallyCapture' => [
                'status' => Status::ACCEPTED,
                'possibleAction' => PossibleAction::PARTIAL_CAPTURE
            ],
            'canRefund' => [
                'status' => Status::ACCEPTED,
                'possibleAction' => PossibleAction::REFUND
            ],
            'canPartiallyRefund' => [
                'status' => Status::ACCEPTED,
                'possibleAction' => PossibleAction::PARTIAL_REFUND
            ]
        ];
    }

    /**
     * Perform actual test for testCanActionMethods.
     *
     * @throws AttributeCombinationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function actuallyTestCanActionMethod(
        string $methodName,
        array $values,
        PossibleAction $case
    ): void {
        if ($case === $values['possibleAction']) {
            $payment = $this->createDummyPayment(
                status: $values['status'],
                possibleActions: new Payment\Order\PossibleActionCollection(
                    data: [new Payment\Order\PossibleAction(
                        action: $values['possibleAction']
                    )]
                )
            );

            $this->assertTrue(condition: $payment->$methodName());
            return;
        }

        $payment = $this->createDummyPayment(
            status: $values['status'],
            possibleActions: new Payment\Order\PossibleActionCollection(
                data: [new Payment\Order\PossibleAction(
                    action: $case
                )]
            )
        );
        $this->assertFalse(condition: $payment->$methodName());
    }

    /**
     * Verify that the isFrozen method works as intended
     *
     * @throws EmptyValueException
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
     * Verify that the can-prefixed methods work as intended.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testCanActionMethods(): void
    {
        foreach ($this->getCanMethodList() as $methodName => $values) {
            foreach (PossibleAction::cases() as $case) {
                $this->actuallyTestCanActionMethod(
                    methodName: $methodName,
                    values: $values,
                    case: $case
                );
            }
        }
    }

    /**
     * CREDIT_DENIED test. Realtime tests can be found in PaymentInformationTest.
     *
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function testIsRejectionReasonCreditDenied(): void
    {
        $isDenied = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::CREDIT_DENIED
        );
        $notDenied = $this->createDummyPayment(status: Status::ACCEPTED);

        $this->assertTrue(
            condition: $isDenied->isRejectionReasonCreditDenied()
        );
        $this->assertTrue(
            condition: $isDenied->isRejected()
        );
        $this->assertFalse(
            condition: $notDenied->isRejectionReasonCreditDenied()
        );
        $this->assertFalse(
            condition: $notDenied->isRejected()
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function testIsRejectionReasonAbortedByCustomer(): void
    {
        $isAborted = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::ABORTED_BY_CUSTOMER
        );

        $this->assertTrue(
            condition: $isAborted->isRejectionReasonAbortedByCustomer()
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsRejectionReasonTimeout(): void
    {
        $isAborted = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::TIMEOUT
        );

        $this->assertTrue(
            condition: $isAborted->isRejectionReasonTimeout()
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsOlderThan(): void
    {
        $olderThanTime = 30;
        $currentTime = new DateTime();
        $createTime = clone $currentTime;
        $createTime->sub(interval: new DateInterval(
            duration: 'PT' . ($olderThanTime + 1) . 'S'
        ));
        $isOlderThan = $this->createDummyPayment(
            status: Status::ACCEPTED,
            created: $createTime->format(format: 'c')
        );

        $this->assertTrue(
            condition: $isOlderThan->isOlderThan(seconds: $olderThanTime)
        );

        $createTime = clone $currentTime;
        $createTime->sub(interval: new DateInterval(
            duration: 'PT' . ($olderThanTime - 1) . 'S'
        ));
        $isNotOlderThan = $this->createDummyPayment(
            status: Status::ACCEPTED,
            created: $createTime->format(format: 'c')
        );

        $this->assertFalse(
            condition: $isNotOlderThan->isOlderThan(seconds: $olderThanTime)
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsRejectionReasonCanceled(): void
    {
        $isAborted = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::CANCELED
        );

        $this->assertTrue(
            condition: $isAborted->isRejectionReasonCanceled()
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsRejectionReasonInsufficientFunds(): void
    {
        $isAborted = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::INSUFFICIENT_FUNDS
        );

        $this->assertTrue(
            condition: $isAborted->isRejectionReasonInsufficientFunds()
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsRejectionReasonTechnicalError(): void
    {
        $isAborted = $this->createDummyPayment(
            status: Status::REJECTED,
            rejectedReasonCategory: RejectedReasonCategory::TECHNICAL_ERROR
        );

        $this->assertTrue(
            condition: $isAborted->isRejectionReasonTechnicalError()
        );
    }
}
