<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection EfferentObjectCouplingInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api\Metadata;

use Exception;
use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TimeoutException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Tests for Metadata updates
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
class PutTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: $this->createMock(type: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );
    }

    /**
     * Make API call to create payment
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @throws AttributeCombinationException
     */
    private function createPayment(string $orderReference): Payment
    {
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
                email: 'test@hosted.resurs.com',
                governmentId: '198305147715',
                mobilePhone: '0701234567',
                deviceInfo: new DeviceInfo()
            ),
            metadata: MockSigner::getMetadata()
        );
    }

    /**
     * Normalize metadata entries to a deterministic key=>value array.
     *
     * @param array<int, Metadata\Entry> $entries
     * @return array<string, mixed>
     */
    private static function normalizeMetadataEntries(array $entries): array
    {
        $normalized = [];

        foreach ($entries as $entry) {
            $normalized[$entry->key] = $entry->value;
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * Verify that Metadata updates work
     *
     * @throws ValidationException
     * @throws CurlException
     * @throws IllegalValueException
     * @throws IllegalTypeException
     * @throws AuthException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ConfigException
     * @throws ApiException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testSimplePut(): void
    {
        $custom = [
            new Metadata\Entry(key: 'foo', value: 'bar'),
        ];

        $custom = array_merge(MockSigner::getMetadataArray(), $custom);

        // Create payment
        $payment = $this->createPayment(
            orderReference: Strings::generateRandomString(length: 12)
        );

        // Sign
        try {
            MockSigner::callCustomerUrl(payment: $payment);
        } catch (TimeoutException $error) {
            if ($_ENV['IS_PIPELINE']) {
                $this->markTestSkipped(
                    message: 'MockSigner failed with timeout.'
                );
            }

            throw $error;
        }

        // Add metadata
        $setMetadataResponse = Repository::setMetadata(
            paymentId: $payment->id,
            metadata: new Metadata(
                custom: new Metadata\EntryCollection(data: $custom)
            )
        );

        // Get payment
        $fetchedPayment = Repository::get(paymentId: $payment->id);

        // Assert that the metadata exists on the fetched payment.
        $expectedMetadata = self::normalizeMetadataEntries(entries: $custom);
        $setMetadataResponseCustom = $setMetadataResponse->custom?->toArray() ?? [];
        $fetchedPaymentCustom = $fetchedPayment->metadata?->custom?->toArray() ?? [];

        $this->assertSame(
            expected: $expectedMetadata,
            actual: self::normalizeMetadataEntries(entries: $setMetadataResponseCustom)
        );
        $this->assertNotNull(actual: $fetchedPayment->metadata);
        $this->assertSame(
            expected: $expectedMetadata,
            actual: self::normalizeMetadataEntries(entries: $fetchedPaymentCustom)
        );
    }
}
