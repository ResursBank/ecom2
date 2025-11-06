<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Http;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\MockSigner;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Repository as PaymentRepository;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Throwable;

/**
 * Controller for the test purchase widget.
 */
class TestPurchaseController extends Controller
{
    /**
     * Performs the test purchase sequence and returns the result as an array.
     *
     * @throws ConfigException
     */
    public function performTest(): array
    {
        $result = [
            'withRefund' => [
                'createPayment' => false,
                'capture' => false,
                'refund' => false
            ],
            'withCancel' => [
                'createPayment' => false,
                'cancel' => false
            ],
            'messages' => []
        ];

        $result = $this->performTestSequenceWithRefund(result: $result);

        return $this->performTestSequenceWithCancel(result: $result);
    }

    /**
     * Attempt to create, capture and refund a payment.
     *
     * @throws ConfigException
     */
    private function performTestSequenceWithRefund(array $result): array
    {
        try {
            // Create payment
            $reference = Strings::generateRandomString(length: 12);
            $payment = $this->createPayment(reference: $reference);

            if ($payment->order?->orderReference !== $reference) {
                throw new TestException(
                    message: 'Reference mismatch when creating payment'
                );
            }

            $result['withRefund']['createPayment'] = true;
            MockSigner::callCustomerUrl(payment: $payment);

            // Capture payment
            $captureResult = PaymentRepository::capture(
                paymentId: $payment->id
            );

            if (
                $captureResult->order?->totalOrderAmount !==
                $captureResult->order?->capturedAmount
            ) {
                throw new TestException(
                    message: 'Captured amount does not match total order amount.'
                );
            }

            $result['withRefund']['capture'] = true;

            // Refund payment
            $refundResult = PaymentRepository::refund(paymentId: $payment->id);

            if (
                $refundResult->order?->totalOrderAmount !==
                $refundResult->order?->refundedAmount
            ) {
                throw new TestException(
                    message: 'Refunded amount does not match total order amount.'
                );
            }

            $result['withRefund']['refund'] = true;
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
            $result['messages'][] = $error->getMessage();
        }

        return $result;
    }

    /**
     * Attempt to create and cancel a payment.
     *
     * @throws ConfigException
     */
    private function performTestSequenceWithCancel(array $result): array
    {
        try {
            // Create second payment
            $reference = Strings::generateRandomString(length: 12);
            $payment = $this->createPayment(reference: $reference);

            if ($payment->order?->orderReference !== $reference) {
                throw new TestException(
                    message: 'Reference mismatch when creating payment'
                );
            }

            $result['withCancel']['createPayment'] = true;
            MockSigner::callCustomerUrl(payment: $payment);

            // Cancel second payment
            $cancelResult = PaymentRepository::cancel(paymentId: $payment->id);

            if (
                $cancelResult->order?->totalOrderAmount !==
                $cancelResult->order?->canceledAmount
            ) {
                throw new TestException(
                    message: 'Canceled amount does not match total order amount.'
                );
            }

            $result['withCancel']['cancel'] = true;
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
            $result['messages'][] = $error->getMessage();
        }

        return $result;
    }

    /**
     * Fetch a payment method ID for use during test.
     *
     * @throws ConfigException
     * @throws Throwable
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function getPaymentMethodId(): string
    {
        $paymentMethods = PaymentMethodRepository::getPaymentMethods();

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $paymentMethods[0];

        return $paymentMethod->getId();
    }

    /**
     * Get order line collection.
     *
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    private function getOrderLines(): OrderLineCollection
    {
        return new OrderLineCollection(
            data: [
                new OrderLine(
                    quantity: 1,
                    quantityUnit: 'st',
                    vatRate: 25,
                    totalAmountIncludingVat: 100
                )
            ]
        );
    }

    /**
     * Get Customer object.
     *
     * @throws IllegalValueException
     */
    private function getCustomer(): Customer
    {
        return new Customer(
            deliveryAddress: new Address(
                addressRow1: 'Glassgatan 15',
                postalArea: 'Göteborg',
                postalCode: '41655',
                countryCode: CountryCode::SE
            ),
            customerType: CustomerType::NATURAL,
            contactPerson: 'Vincent',
            email: 'test@hosted.resurs.com',
            governmentId: '198305147715',
            mobilePhone: '0701234567',
            deviceInfo: new Customer\DeviceInfo()
        );
    }

    /**
     * Create a new payment.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @throws NotJsonEncodedException
     */
    private function createPayment(string $reference): Payment
    {
        return PaymentRepository::create(
            paymentMethodId: $this->getPaymentMethodId(),
            orderLines: $this->getOrderLines(),
            orderReference: $reference,
            customer: $this->getCustomer(),
            metadata: MockSigner::getMetadata()
        );
    }
}
