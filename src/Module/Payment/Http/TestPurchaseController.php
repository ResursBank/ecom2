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
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\MockSigner;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Repository as PaymentRepository;
use Resursbank\Ecom\Module\Widget\TestPurchase\Html;
use Throwable;

/**
 * Controller for the test purchase widget.
 */
class TestPurchaseController extends Controller
{
    private CountryCode $countryCode;
    /**
     * Performs the test purchase sequence and returns the result as an array.
     *
     * @throws ConfigException
     */
    public function performTest(CountryCode $countryCode): array
    {
        $this->countryCode = $countryCode;
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
        return match ($this->countryCode) {
            CountryCode::SE => $this->getSwedishCustomer(),
            CountryCode::DK => $this->getDanishCustomer(),
            CountryCode::NO => $this->getNorwegianCustomer(),
            CountryCode::FI => $this->getFinnishCustomer(),
            default => throw new IllegalValueException(
                message: 'Illegal country code.'
            )
        };
    }

    /**
     * Get Swedish customer.
     *
     * @throws IllegalValueException
     */
    private function getSwedishCustomer(): Customer
    {
        return new Customer(
            deliveryAddress: new Address(
                addressRow1: 'Glassgatan 15',
                postalArea: 'Göteborg',
                postalCode: '41655',
                countryCode: CountryCode::SE
            ),
            customerType: CustomerType::NATURAL,
            contactPerson: 'Vincent Alexandersson',
            email: 'test@hosted.resurs.com',
            governmentId: '198305147715',
            mobilePhone: '0701234567',
            deviceInfo: new Customer\DeviceInfo()
        );
    }

    /**
     * Get Danish customer.
     *
     * @throws IllegalValueException
     */
    private function getDanishCustomer(): Customer
    {
        return new Customer(
            deliveryAddress: new Address(
                addressRow1: 'Strøget 15',
                postalArea: 'Hornbæk',
                postalCode: '3100',
                countryCode: CountryCode::DK
            ),
            customerType: CustomerType::NATURAL,
            contactPerson: 'Gorm Anker Bøgh',
            email: 'test@hosted.resurs.com',
            governmentId: '140285-3877',
            mobilePhone: '4525557585',
            deviceInfo: new Customer\DeviceInfo()
        );
    }

    /**
     * Get Norwegian customer.
     *
     * @throws IllegalValueException
     */
    private function getNorwegianCustomer(): Customer
    {
        return new Customer(
            deliveryAddress: new Address(
                addressRow1: 'Strøget 15',
                postalArea: 'Stavanger',
                postalCode: '3100',
                countryCode: CountryCode::NO
            ),
            customerType: CustomerType::NATURAL,
            contactPerson: 'Gorm Anker Bøgh',
            email: 'test@hosted.resurs.com',
            governmentId: '180872-48794',
            mobilePhone: '49999999',
            deviceInfo: new Customer\DeviceInfo()
        );
    }

    /**
     * Get Finnish customer.
     *
     * @throws IllegalValueException
     */
    private function getFinnishCustomer(): Customer
    {
        return new Customer(
            deliveryAddress: new Address(
                addressRow1: 'Kansakoulukatu 90',
                postalArea: 'Helsinki',
                postalCode: '0100',
                countryCode: CountryCode::FI
            ),
            customerType: CustomerType::NATURAL,
            contactPerson: 'Olavi Korhonen Nieminen',
            email: 'test@hosted.resurs.com',
            governmentId: '230580-7335',
            mobilePhone: '3585005555127',
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
            paymentMethodId: Html::getPaymentMethodId(),
            orderLines: $this->getOrderLines(),
            orderReference: $reference,
            customer: $this->getCustomer(),
            metadata: MockSigner::getMetadata()
        );
    }
}
