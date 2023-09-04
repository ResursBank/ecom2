<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Options;
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
     * @throws IllegalValueException
     * @throws EmptyValueException
     * @throws Exception
     */
    private function generateCheckoutModel(
        ?string $id = null,
        ?string $storeId = null,
        ?string $orderReference = null,
        ?string $version = null
    ): void {
        new Checkout(
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
            )
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
     * @throws EmptyValueException
     * @throws IllegalValueException
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
     * @throws EmptyValueException
     * @throws IllegalValueException
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
     * @throws IllegalValueException
     * @throws EmptyValueException
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
     * @throws EmptyValueException
     * @throws IllegalValueException
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
}
