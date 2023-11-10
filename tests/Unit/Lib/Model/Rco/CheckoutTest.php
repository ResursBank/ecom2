<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActions;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActionsCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentSelection as PaymentSelectionEnum;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus as PaymentStatusEnum;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\Options;
use Resursbank\Ecom\Lib\Model\Rco\Payment;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentSelection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentStatus;
use Resursbank\Ecom\Lib\Model\Rco\Status;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Throwable;

/**
 * Integrity test of RCO Checkout model class.
 */
class CheckoutTest extends TestCase
{
    /**
     * Get mocked Checkout model instance.
     *
     * @param string|null $id
     * @param string|null $storeId
     * @param string|null $orderReference
     * @param string|null $version
     * @param Payment|null $payment
     * @return Checkout
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    private function generateCheckoutModel(
        ?string $id = null,
        ?string $storeId = null,
        ?string $orderReference = null,
        ?string $version = null,
        ?Payment $payment = null
    ): Checkout {
        return new Checkout(
            id: $id ?? Strings::getUuid(),
            storeId: $storeId ?? Strings::getUuid(),
            orderReference: $orderReference ?? Strings::generateRandomString(
                length: 32
            ),
            countryCode: CountryCode::SE,
            locale: Locale::sv_SE,
            currency: Currency::SEK,
            version: $version ?? Strings::getUuid(),
            options: new Options(),
            customer: new Customer(type: Type::B2C),
            status: new Status(
                type: CheckoutStatus::INITIATED
            ),
            payment: $payment ?? $this->generatePayment()
        );
    }

    /**
     * Generates a Payment object.
     *
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws Exception
     */
    private function generatePayment(
        ?PaymentStatus $paymentStatus = null
    ): Payment {
        $paymentMethodId = Strings::getUuid();
        return new Payment(
            methods: new PaymentMethodCollection(data: [
                new PaymentMethod(
                    methodId: $paymentMethodId,
                    name: Strings::generateRandomString(length: 12),
                    type: PaymentMethod\Type::GENERIC,
                    fee: 0,
                    required: new RequiredCollection(data: [
                        Required::GOVERNMENT_ID
                    ]),
                    subtitle: Strings::generateRandomString(length: 32),
                    descriptions: [
                        Strings::generateRandomString(length: 32)
                    ],
                    terms: Strings::generateRandomString(length: 32),
                    links: new PaymentMethod\LinkCollection(data: [
                        new PaymentMethod\Link(
                            label: Strings::generateRandomString(length: 12),
                            url: 'https://example.com'
                        )
                    ])
                )
            ]),
            selection: new PaymentSelection(
                methodId: $paymentMethodId,
                type: PaymentSelectionEnum::DEFAULT
            ),
            status: $paymentStatus ?? new PaymentStatus(
                requestedAmount: 0,
                authorizedAmount: 0,
                cancelledAmount: 0,
                capturedAmount: 0,
                refundedAmount: 0,
                availableActions: new AvailableActionsCollection(data: []),
                type: PaymentStatusEnum::NONE
            )
        );
    }

    /**
     * Generates a PaymentStatus object.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function generatePaymentStatus(
        ?int $requestedAmount = null,
        ?int $authorizedAmount = null,
        ?int $cancelledAmount = null,
        ?int $capturedAmount = null,
        ?int $refundedAmount = null,
        ?AvailableActionsCollection $availableActions = null
    ): PaymentStatus {
        return new PaymentStatus(
            requestedAmount: $requestedAmount ?? 0,
            authorizedAmount: $authorizedAmount ?? 0,
            cancelledAmount: $cancelledAmount ?? 0,
            capturedAmount: $capturedAmount ?? 0,
            refundedAmount: $refundedAmount ?? 0,
            availableActions: $availableActions ?? new AvailableActionsCollection(data: []),
            type: PaymentStatusEnum::NONE
        );
    }

    /**
     * Test generating a valid Checkout model instance.
     */
    public function testCheckoutModel(): void
    {
        try {
            $this->generateCheckoutModel();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Failed to generate Checkout model instance.');
        }
    }

    /**
     * Assert validation rules for id property.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testIdValidation(): void
    {
        try {
            $this->generateCheckoutModel(id: '');
            $this->fail(message: 'Empty id value accepted.');
        } catch (EmptyValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateCheckoutModel(id: 'not-a-uuid');
            $this->fail(message: 'Invalid id value accepted.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }
    }

    /**
     * Assert validation rules for storeId property.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testStoreIdValidation(): void
    {
        try {
            $this->generateCheckoutModel(storeId: '');
            $this->fail(message: 'Empty storeId value accepted.');
        } catch (EmptyValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateCheckoutModel(storeId: 'asd-dcvb-123saqd-asd2-wdsf');
            $this->fail(message: 'Invalid storeId value accepted.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }
    }

    /**
     * Assert validation rules for orderReference property.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testOrderReferenceValidation(): void
    {
        try {
            $this->generateCheckoutModel(orderReference: '');
            $this->fail(message: 'Empty orderReference value accepted.');
        } catch (EmptyValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateCheckoutModel(
                orderReference: Strings::generateRandomString(length: 32)
            );

            $this->addToAssertionCount(count: 1);
        } catch (IllegalCharsetException) {
            $this->fail(message: '32 character orderReference value rejected.');
        }

        try {
            $this->generateCheckoutModel(
                orderReference: Strings::generateRandomString(length: 33)
            );

            $this->fail(message: '33 character orderReference value accepted.');
        } catch (IllegalCharsetException) {
            $this->addToAssertionCount(count: 1);
        }
    }

    /**
     * Assert validation rules for version property.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testVersionValidation(): void
    {
        try {
            $this->generateCheckoutModel(version: '');
            $this->fail(message: 'Empty version value accepted.');
        } catch (EmptyValueException) {
            $this->addToAssertionCount(count: 1);
        }

        try {
            $this->generateCheckoutModel(version: '123');
            $this->fail(message: 'Invalid version value accepted.');
        } catch (IllegalValueException) {
            $this->addToAssertionCount(count: 1);
        }
    }

    /**
     * Verify that the canCapture method works as intended.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testCanCapture(): void
    {
        foreach (AvailableActions::cases() as $action) {
            $checkout = $this->generateCheckoutModel(
                payment: $this->generatePayment(
                    paymentStatus: $this->generatePaymentStatus(
                        availableActions: new AvailableActionsCollection(
                            data: [
                                $action
                            ]
                        )
                    )
                )
            );

            if ($action === AvailableActions::CAPTURE) {
                $this->assertTrue(
                    condition: $checkout->canCapture()
                );
            } else {
                $this->assertFalse(
                    condition: $checkout->canCapture()
                );
            }
        }
    }

    /**
     * Verify that the canCancel method works as intended.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testCanCancel(): void
    {
        foreach (AvailableActions::cases() as $action) {
            $checkout = $this->generateCheckoutModel(
                payment: $this->generatePayment(
                    paymentStatus: $this->generatePaymentStatus(
                        availableActions: new AvailableActionsCollection(
                            data: [
                                $action
                            ]
                        )
                    )
                )
            );

            if ($action === AvailableActions::CANCEL) {
                $this->assertTrue(
                    condition: $checkout->canCancel()
                );
            } else {
                $this->assertFalse(
                    condition: $checkout->canCancel()
                );
            }
        }
    }

    /**
     * Verify that the canRefund method works as intended.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testCanRefund(): void
    {
        foreach (AvailableActions::cases() as $action) {
            $checkout = $this->generateCheckoutModel(
                payment: $this->generatePayment(
                    paymentStatus: $this->generatePaymentStatus(
                        availableActions: new AvailableActionsCollection(
                            data: [
                                $action
                            ]
                        )
                    )
                )
            );

            if ($action === AvailableActions::REFUND) {
                $this->assertTrue(
                    condition: $checkout->canRefund()
                );
            } else {
                $this->assertFalse(
                    condition: $checkout->canRefund()
                );
            }
        }
    }

    /**
     * Verify that the isCaptured method works as intended.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testIsCaptured(): void
    {
        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    authorizedAmount: 500,
                    capturedAmount: 500,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertTrue(
            condition: $checkout->isCaptured()
        );

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    authorizedAmount: 0,
                    capturedAmount: 500,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::CAPTURE
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isCaptured());

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    authorizedAmount: 500,
                    capturedAmount: 0,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isCaptured());

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    authorizedAmount: 0,
                    capturedAmount: 500,
                    refundedAmount: 500,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isCaptured());
    }

    /**
     * Verify that the isCancelled method works as intended.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testIsCancelled(): void
    {
        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    requestedAmount: 500,
                    authorizedAmount: 500,
                    cancelledAmount: 500,
                    capturedAmount: 0,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertTrue(condition: $checkout->isCancelled());

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    requestedAmount: 250,
                    authorizedAmount: 500,
                    cancelledAmount: 250,
                    capturedAmount: 0,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isCancelled());

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    requestedAmount: 250,
                    authorizedAmount: 0,
                    cancelledAmount: 500,
                    capturedAmount: 0,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isCancelled());
    }

    /**
     * Verify that the isRefunded method works as intended.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testIsRefunded(): void
    {
        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    requestedAmount: 500,
                    authorizedAmount: 500,
                    cancelledAmount: 0,
                    capturedAmount: 500,
                    refundedAmount: 500,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::CANCEL
                    ])
                )
            )
        );

        $this->assertTrue(condition: $checkout->isRefunded());

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    requestedAmount: 250,
                    authorizedAmount: 0,
                    cancelledAmount: 00,
                    capturedAmount: 0,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isRefunded());

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    requestedAmount: 500,
                    authorizedAmount: 250,
                    cancelledAmount: 0,
                    capturedAmount: 250,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isRefunded());

        $checkout = $this->generateCheckoutModel(
            payment: $this->generatePayment(
                paymentStatus: $this->generatePaymentStatus(
                    requestedAmount: 500,
                    authorizedAmount: 0,
                    cancelledAmount: 0,
                    capturedAmount: 250,
                    refundedAmount: 230,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::REFUND
                    ])
                )
            )
        );

        $this->assertFalse(condition: $checkout->isRefunded());
    }
}
