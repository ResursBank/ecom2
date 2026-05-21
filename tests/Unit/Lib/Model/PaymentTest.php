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
use Resursbank\Ecom\Lib\Model\Payment\Enum\PossibleAction;
use Resursbank\Ecom\Lib\Model\Payment\Enum\RejectedReasonCategory;
use Resursbank\Ecom\Lib\Model\Payment\Enum\Status;
use Resursbank\Ecom\Lib\Model\Payment\Order\PossibleAction as OrderPossibleAction;
use Resursbank\Ecom\Lib\Model\Payment\Order\PossibleActionCollection;
use Resursbank\Ecom\Lib\Model\Payment\RejectedReason;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Tests for the Resursbank\Ecom\Lib\Model\Payment class.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
// phpcs:ignoreFile
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
        ?string $created = null,
        float $authorizedAmount = 100.00,
        float $capturedAmount = 0.00,
        float $refundedAmount = 0.00,
        float $canceledAmount = 0.00,
        float $totalOrderAmount = 100.00
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
                totalOrderAmount: $totalOrderAmount,
                canceledAmount: $canceledAmount,
                authorizedAmount: $authorizedAmount,
                capturedAmount: $capturedAmount,
                refundedAmount: $refundedAmount
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
     * Verify behavior of isProcessable.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testIsProcessable(): void
    {
        foreach (Status::cases() as $case) {
            $dummyPayment = $this->createDummyPayment(status: $case);

            if (
                $dummyPayment->status === Status::ACCEPTED ||
                $dummyPayment->status === Status::TASK_REDIRECTION_REQUIRED
            ) {
                $this->assertTrue(condition: $dummyPayment->isProcessable());
            }

            if (
                $dummyPayment->status === Status::ACCEPTED ||
                $dummyPayment->status === Status::TASK_REDIRECTION_REQUIRED
            ) {
                continue;
            }

            $this->assertFalse(condition: $dummyPayment->isProcessable());
        }
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

    /**
     * Verify behavior of isCaptured.
     *
     * @throws AttributeCombinationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testIsCaptured(): void
    {
        foreach (Status::cases() as $case) {
            foreach (PossibleAction::cases() as $action) {
                foreach ([0.00, 100.00] as $authorizedAmount) {
                    foreach ([0.00, 100.00] as $capturedAmount) {
                        $dummyPayment = $this->createDummyPayment(
                            status: $case,
                            possibleActions: new PossibleActionCollection(
                                data: [
                                    new OrderPossibleAction(action: $action)
                                ]
                            ),
                            authorizedAmount: $authorizedAmount,
                            capturedAmount: $capturedAmount
                        );

                        if (
                            !$dummyPayment->canCapture() &&
                            !$dummyPayment->canPartiallyCapture() &&
                            // @phpstan-ignore-next-line
                            $dummyPayment->order->authorizedAmount === 0.0 &&
                            // @phpstan-ignore-next-line
                            $dummyPayment->order->capturedAmount > 0.0 &&
                            // @phpstan-ignore-next-line
                            $dummyPayment->order->capturedAmount !==
                            // @phpstan-ignore-next-line
                            $dummyPayment->order->refundedAmount
                        ) {
                            $this->assertTrue(
                                condition: $dummyPayment->isCaptured()
                            );
                        } else {
                            $this->assertFalse(
                                condition: $dummyPayment->isCaptured()
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Verify behavior of isRefunded.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testIsRefunded(): void
    {
        foreach ([0.00, 100.00] as $authorizedAmount) {
            foreach ([0.00, 100.00] as $capturedAmount) {
                foreach ([0.00, 100.00] as $refundedAmount) {
                    $dummyPayment = $this->createDummyPayment(
                        status: Status::ACCEPTED,
                        authorizedAmount: $authorizedAmount,
                        capturedAmount: $capturedAmount,
                        refundedAmount: $refundedAmount
                    );

                    if (
                        // @phpstan-ignore-next-line
                        $dummyPayment->order->authorizedAmount === 0.0 &&
                        // @phpstan-ignore-next-line
                        $dummyPayment->order->capturedAmount > 0.0 &&
                        // @phpstan-ignore-next-line
                        $dummyPayment->order->capturedAmount ===
                        // @phpstan-ignore-next-line
                        $dummyPayment->order->refundedAmount
                    ) {
                        $this->assertTrue(
                            condition: $dummyPayment->isRefunded()
                        );
                    } else {
                        $this->assertFalse(
                            condition: $dummyPayment->isRefunded()
                        );
                    }
                }
            }
        }
    }

    /**
     * Verify the behavior of the isCancelled method.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testIsCancelled(): void
    {
        foreach (Status::cases() as $case) {
            foreach (RejectedReasonCategory::cases() as $reason) {
                foreach ([0.00, 100.00] as $authorizedAmount) {
                    foreach ([0.00, 100.00] as $canceledAmount) {
                        foreach ([0.00, 100.00] as $totalOrderAmount) {
                            $dummyPayment = $this->createDummyPayment(
                                status: $case,
                                rejectedReasonCategory: $reason,
                                authorizedAmount: $authorizedAmount,
                                canceledAmount: $canceledAmount,
                                totalOrderAmount: $totalOrderAmount
                            );

                            if (
                                (
                                    // @phpstan-ignore-next-line
                                    $dummyPayment->order->authorizedAmount === 0.0 &&
                                    // @phpstan-ignore-next-line
                                    $dummyPayment->order->canceledAmount === $dummyPayment->order->totalOrderAmount
                                ) ||
                                (
                                    $dummyPayment->status === Status::REJECTED &&
                                    $dummyPayment->rejectedReason?->category === RejectedReasonCategory::CANCELED
                                )
                            ) {
                                $this->assertTrue(
                                    condition: $dummyPayment->isCancelled()
                                );
                            } else {
                                $this->assertFalse(
                                    condition: $dummyPayment->isCancelled()
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
