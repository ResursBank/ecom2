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
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Rco\Cart;
use Resursbank\Ecom\Lib\Model\Rco\CheckboxCollection;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Contact;
use Resursbank\Ecom\Lib\Model\Rco\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Customer\TypeCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActions;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActionsCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentSelection as PaymentSelectionEnum;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus as PaymentStatusEnum;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\ShippingSelection;
use Resursbank\Ecom\Lib\Model\Rco\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Options;
use Resursbank\Ecom\Lib\Model\Rco\Payment;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentSelection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentStatus;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Lib\Model\Rco\Shipping;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Method;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Status;
use Resursbank\Ecom\Lib\Model\Rco\Tracking;
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
        $shippingSelection = new Shipping\Selection(
            methodId: Strings::getUuid(),
            optionId: Strings::getUuid(),
            type: ShippingSelection::DEFAULT
        );
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
            options: new Options(
                renderCart: true,
                calculateShipping: true,
                lookupB2CAddress: true,
                renderCartCode: true,
                renderNotes: true,
                allowDelayedAuthorization: true
            ),
            customer: new Customer(
                type: Type::B2C,
                governmentId: 'SE' . $_ENV['RCO_JWT_GOVERNMENT_ID'],
                billing: new Recipient(
                    name: Strings::generateRandomString(length: 12),
                    contact: new Contact(
                        firstName: Strings::generateRandomString(length: 32),
                        lastName: Strings::generateRandomString(length: 32),
                        phone: '+46701234567',
                        email: Strings::generateRandomString(length: 32)
                    )
                ),
                delivery: new Recipient(
                    name: Strings::generateRandomString(length: 12),
                    contact: new Contact(
                        firstName: Strings::generateRandomString(length: 32),
                        lastName: Strings::generateRandomString(length: 32),
                        phone: '+46701234567',
                        email: Strings::generateRandomString(length: 32)
                    )
                )
            ),
            status: new Status(
                type: CheckoutStatus::INITIATED
            ),
            payment: $payment ?? $this->generatePayment(),
            cart: new Cart(
                items: new Cart\ItemCollection(data: []),
                code: Strings::generateRandomString(length: 12)
            ),
            checkboxes: new CheckboxCollection(data: []),
            merchant: new Merchant(
                displayName: Strings::generateRandomString(length: 12),
                logoUrl: 'https://example.com',
                termsUrl: 'https://example.com',
                homepageUrl: 'https://example.com'
            ),
            shipping: new Shipping(
                tracking: new Tracking(
                    url: 'https://example.com'
                ),
                selection: $shippingSelection,
                methods: new Shipping\MethodCollection(data: [
                    new Method(
                        methodId: $shippingSelection->methodId,
                        description: Strings::generateRandomString(length: 12),
                        type: Shipping\Type::DELIVERY,
                        carrier: Carrier::GENERIC,
                        name: Strings::generateRandomString(length: 12),
                        deliveryEta: Strings::generateRandomString(length: 12),
                        price: new Shipping\Price(
                            display: '5,00',
                            calculateTax: 100,
                            calculate: 500
                        ),
                        options: new OptionCollection(data: []),
                        required: new RequiredCollection(data: [])
                    )
                ])
            ),
            notes: ''
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
                    ]),
                    customerTypes: new TypeCollection(
                        data: [Type::B2C, Type::B2B]
                    ),
                    minLimit: 10,
                    maxLimit: 50000
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
            availableActions: $availableActions ?? new AvailableActionsCollection(
                data: []
            ),
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
