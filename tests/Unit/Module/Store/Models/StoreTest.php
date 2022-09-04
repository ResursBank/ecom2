<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Store\Models;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\EcomTest\Data\GetStores;
use stdClass;

/**
 * Test data integrity of store entity model.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class StoreTest extends TestCase
{
    /**
     * @var Store
     */
    private Store $item;

    /**
     * @var stdClass
     */
    private stdClass $data;

    /**
     * @return void
     * @throws JsonException
     * @throws TestException
     */
    protected function setUp(): void
    {
        $this->data = GetStores::getRandomStoreData();

        parent::setUp();
    }

    /**
     * @param array $updates
     * @return void
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalTypeException
     */
    private function convert(
        array $updates = []
    ): void {
        /** @psalm-suppress MixedAssignment */
        foreach ($updates as $key => $val) {
            $this->data->{$key} = $val;
        }

        $item = DataConverter::stdClassToType(
            object: $this->data,
            type: Store::class
        );

        if (!$item instanceof Store) {
            throw new TestException(
                message: 'Conversion succeeded but did not return Method instance.'
            );
        }

        $this->item = $item;
    }

    /**
     * Assert validateId() throws EmptyValueException when id is empty.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateIdThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['id' => '']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testIdAssigned(): void
    {
        $this->convert();
        self::assertSame(expected: $this->data->id, actual: $this->item->id);
    }

    /**
     * Assert validateNationalStoreId() throws IllegalValueException when
     * nationalStoreId is 0.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testNationalStoreIdThrowsWithZeroValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['nationalStoreId' => 0]);
    }

    /**
     * Assert validateNationalStoreId() throws IllegalValueException when
     * nationalStoreId is negative.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testNationalStoreIdThrowsWithNegativeValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['nationalStoreId' => -1]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testNationalStoreIdWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->nationalStoreId,
            actual: $this->item->nationalStoreId
        );
    }

    /**
     * Assert validateId() throws IllegalValueException when id is not a valid
     * uuid.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateIdThrowsWithoutUuid(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['id' => 'not-a-uuid']);
    }

    /**
     * Assert validateCountryCode() accepts values  SE, NO, FI, DK.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateCountryCodeAcceptsAlpha2CountryCode(): void
    {
        $this->convert(updates: ['countryCode' => 'SE']);
        self::assertSame(expected: 'SE', actual: $this->item->countryCode);
    }

    /**
     * Assert validateCountryCode() throws IllegalCharsetException for values
     * like SWE, NOR, FIN, DAN.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateCountryCodeThrowsOnAlpha3CountryCode(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->convert(updates: ['countryCode' => 'SWE']);
    }

    /**
     * Assert validateCountryCode() throws IllegalCharsetException with illegal
     * charset.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateCountryCodeThrowsWithIllegalCharset(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->convert(updates: ['countryCode' => 'no']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testCountryCodeWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->countryCode,
            actual: $this->item->countryCode
        );
    }

    /**
     * Assert validateName() throws EmptyValueException when name is empty.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateNameThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['name' => '']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testNameWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->name,
            actual: $this->item->name
        );
    }
}
