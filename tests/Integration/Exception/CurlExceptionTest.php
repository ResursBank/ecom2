<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Verifies that the CurlException class works as intended when resolving
 * dynamic error content.
 */
class CurlExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        parent::setUp();
    }

    /**
     * Make API call to create payment.
     */
    protected function createPayment(
        string $orderReference,
        string $mobilePhone = '0701234567',
        string $governmentId = '198305147715',
        string $email = 'test@hosted.resurs.com'
    ): Payment {
        /** @noinspection DuplicatedCode */
        return Repository::create(
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: new OrderLineCollection(data: [
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Android',
                    reference: 'T-800',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Robot',
                    reference: 'T-1000',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
            ]),
            orderReference: $orderReference,
            customer: new Customer(
                deliveryAddress: new Address(
                    addressRow1: 'Glassgatan 15',
                    postalArea: 'Göteborg',
                    postalCode: '41655',
                    countryCode: CountryCode::SE
                ),
                customerType: CustomerType::NATURAL,
                contactPerson: 'Vincent',
                email: $email,
                governmentId: $governmentId,
                mobilePhone: $mobilePhone,
                deviceInfo: new DeviceInfo()
            ),
            metadata: MockSigner::getMetadata()
        );
    }

    /**
     * Assert getDetailedMessage appends validation info.
     *
     * @throws Exception
     */
    public function testGetDetailedMessage(): void
    {
        // Test that an invalid government ID fails. This is a value which will
        // pass our own validation in ECom because of formatting, but will be
        // rejected by the Resurs Bank API which performs stricter checks.
        try {
            $this->createPayment(
                orderReference: Strings::generateRandomString(length: 12),
                governmentId: '198123112311'
            );

            // CurlException was expected.
            $this->fail(message: 'Expected CurlException was not thrown.');
        } catch (CurlException $e) {
            // Assert that getDetailedMessage returns a string that is different
            // from both the original exception message and the input message,
            // yet begins with the input message, proving that details were added.
            $detailedMessage = $e->getDetailedMessage(msg: 'Test message.');

            $this->assertIsString(actual: $detailedMessage);
            $this->assertNotSame(
                expected: $e->getMessage(),
                actual: $detailedMessage
            );
            $this->assertNotSame(
                expected: 'Test message.',
                actual: $detailedMessage
            );
            $this->assertStringStartsWith(
                prefix: 'Test message.',
                string: $detailedMessage
            );
        }

        // Test that an invalid phone number fails. This is a value which will
        // pass our own validation in ECom because of formatting, but will be
        // rejected by the Resurs Bank API which performs stricter checks.
        try {
            $this->createPayment(
                orderReference: Strings::generateRandomString(length: 12),
                mobilePhone: '0809910000'
            );

            // CurlException was expected.
            $this->fail(
                message: 'Expected CurlException was not thrown for invalid phone number.'
            );
        } catch (CurlException $e) {
            $detailedMessage = $e->getDetailedMessage(
                msg: 'Test message (phone).'
            );
            $this->assertIsString(actual: $detailedMessage);
            $this->assertNotSame(
                expected: $e->getMessage(),
                actual: $detailedMessage
            );
            $this->assertNotSame(
                expected: 'Test message (phone).',
                actual: $detailedMessage
            );
            $this->assertStringStartsWith(
                prefix: 'Test message (phone).',
                string: $detailedMessage
            );
        }

        // Test that an invalid email address fails. This is a value which will
        // pass our own validation in ECom because of formatting, but will be
        // rejected by the Resurs Bank API which performs stricter checks.
        try {
            $this->createPayment(
                orderReference: Strings::generateRandomString(length: 12),
                email: 'asd@difjgod903845gn.asdasd'
            );

            // CurlException was expected.
            $this->fail(
                message: 'Expected CurlException was not thrown for invalid email.'
            );
        } catch (CurlException $e) {
            $detailedMessage = $e->getDetailedMessage(
                msg: 'Test message (email).'
            );
            $this->assertIsString(actual: $detailedMessage);
            $this->assertNotSame(
                expected: $e->getMessage(),
                actual: $detailedMessage
            );
            $this->assertNotSame(
                expected: 'Test message (email).',
                actual: $detailedMessage
            );
            $this->assertStringStartsWith(
                prefix: 'Test message (email).',
                string: $detailedMessage
            );
        }
    }
}
