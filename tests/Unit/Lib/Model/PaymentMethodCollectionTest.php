<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingValueException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLinkCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Tests for the Resursbank\Ecom\Lib\Model\PaymentCollection class.
 */
class PaymentMethodCollectionTest extends TestCase
{
    /**
     * Generate a payment method.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function generateModel(
        string $id,
        string $name,
        Type $type,
        bool $enabledForLegalCustomer = false
    ): PaymentMethod {
        return new PaymentMethod(
            id: $id,
            name: $name,
            type: $type,
            minPurchaseLimit: 1,
            maxPurchaseLimit: 1000,
            minApplicationLimit: 1,
            maxApplicationLimit: 1000,
            legalLinks: new LegalLinkCollection(data: []),
            enabledForLegalCustomer: $enabledForLegalCustomer,
            enabledForNaturalCustomer: true,
            priceSignagePossible: true,
            sortOrder: 1
        );
    }

    /**
     * Assert that we can resolve name of payment method from collection.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     * @noinspection DuplicatedCode
     */
    public function testGetMethodName(): void
    {
        $id1 = Strings::getUuid();
        $name1 = Strings::generateRandomString(10);

        $id2 = Strings::getUuid();
        $name2 = Strings::generateRandomString(10);

        $id3 = Strings::getUuid();
        $name3 = Strings::generateRandomString(10);

        $collection = new PaymentMethodCollection(data: [
            $this->generateModel($id1, $name1, Type::RESURS_REVOLVING_CREDIT),
            $this->generateModel($id2, $name2, Type::RESURS_PART_PAYMENT),
            $this->generateModel($id3, $name3, Type::RESURS_CARD),
        ]);

        $this->assertEquals($name1, $collection->getMethodName($id1));
        $this->assertEquals($name2, $collection->getMethodName($id2));
        $this->assertEquals($name3, $collection->getMethodName($id3));

        // Assert id is returned if method is not found.
        $this->assertEquals('unknown', $collection->getMethodName('unknown'));
    }

    /**
     * Assert we can search collection for payment method by id.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws MissingValueException
     * @throws Exception
     * @noinspection DuplicatedCode
     */
    public function testGetById(): void
    {
        $id1 = Strings::getUuid();
        $name1 = Strings::generateRandomString(10);

        $id2 = Strings::getUuid();
        $name2 = Strings::generateRandomString(10);

        $id3 = Strings::getUuid();
        $name3 = Strings::generateRandomString(10);

        $collection = new PaymentMethodCollection(data: [
            $this->generateModel($id1, $name1, Type::RESURS_REVOLVING_CREDIT),
            $this->generateModel($id2, $name2, Type::RESURS_PART_PAYMENT),
            $this->generateModel($id3, $name3, Type::RESURS_CARD),
        ]);

        $this->assertEquals($id1, $collection->getById($id1)->id);
        $this->assertEquals($id2, $collection->getById($id2)->id);
        $this->assertEquals($id3, $collection->getById($id3)->id);
    }

    /**
     * Assert that getById throws exception if method is not found.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws MissingValueException
     */
    public function testGetByIdThrows(): void
    {
        $this->expectException(MissingValueException::class);

        $collection = new PaymentMethodCollection(data: []);
        $collection->getById(Strings::getUuid());
    }

    /**
     * Assert that we can detect whether a b2b method i present in a collection.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    public function testHasB2bMethod(): void
    {
        // Confirm that we can detect a B2B method.
        $collection = new PaymentMethodCollection(data: [
            $this->generateModel(
                Strings::getUuid(),
                Strings::generateRandomString(10),
                Type::RESURS_PART_PAYMENT,
                false
            ),
            $this->generateModel(
                Strings::getUuid(),
                Strings::generateRandomString(10),
                Type::RESURS_REVOLVING_CREDIT,
                true
            ),
            $this->generateModel(
                Strings::getUuid(),
                Strings::generateRandomString(10),
                Type::RESURS_CARD,
                false
            ),
        ]);

        $this->assertTrue($collection->hasB2bMethod());

        // Confirm that we get false when no B2B method is present.
        $collection = new PaymentMethodCollection(data: [
            $this->generateModel(
                Strings::getUuid(),
                Strings::generateRandomString(10),
                Type::RESURS_PART_PAYMENT,
                false
            ),
            $this->generateModel(
                Strings::getUuid(),
                Strings::generateRandomString(10),
                Type::RESURS_REVOLVING_CREDIT,
                false
            ),
            $this->generateModel(
                Strings::getUuid(),
                Strings::generateRandomString(10),
                Type::RESURS_CARD,
                false
            ),
        ]);

        $this->assertFalse($collection->hasB2bMethod());
    }
}
