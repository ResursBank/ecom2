<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Store;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Model\Store\Store;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Store\Enum\Country;

/**
 * Test data integrity of store entity model.
 */
class StoreTest extends TestCase
{
    /** @var array<string, mixed> */
    private static array $data = [
        'id' => 'db51fe4f-a74d-4025-9d1d-a49b7aa0fde5',
        'nationalStoreId' => 8902,
        'countryCode' => 'SE',
        'name' => 'Testing',
    ];

    /**
     * @param array<string, mixed> $updates
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     * @throws IllegalValueException
     */
    private function convert(
        array $updates = []
    ): Store {
        $result = DataConverter::stdClassToType(
            object: (object) array_merge(self::$data, $updates),
            type: Store::class
        );

        if (!$result instanceof Store) {
            throw new TestException(
                message: 'Failed to convert stdClass to Store.'
            );
        }

        return $result;
    }

    /**
     * Assert validateId() throws EmptyValueException when id is empty.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateIdThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['id' => '']);
    }

    /**
     * Assert validateId() throws IllegalValueException when id is not a valid
     * uuid.
     *
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateIdThrowsWithoutUuid(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['id' => 'not-a-uuid']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testIdAssigned(): void
    {
        $store = $this->convert();
        $this->assertSame(expected: self::$data['id'], actual: $store->id);
    }

    /**
     * Assert validateNationalStoreId() throws IllegalValueException when
     * nationalStoreId is 0.
     *
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
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testNationalStoreIdWasAssigned(): void
    {
        $store = $this->convert();
        $this->assertSame(
            expected: self::$data['nationalStoreId'],
            actual: $store->nationalStoreId
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testCountryCodeWasAssigned(): void
    {
        $store = $this->convert();
        $this->assertSame(expected: Country::SE, actual: $store->countryCode);
    }

    /**
     * Assert validateName() throws EmptyValueException when name is empty.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateNameThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['name' => '']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testNameWasAssigned(): void
    {
        $store = $this->convert();
        $this->assertSame(expected: self::$data['name'], actual: $store->name);
    }

    /***
     * Assert getLanguage() returns the correct language based on country code.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testGetLanguage(): void
    {
        $cases = [
            Country::SE->name => Language::SV,
            Country::FI->name => Language::FI,
            Country::NO->name => Language::NO,
            Country::DK->name => Language::DA,
            Country::UNKNOWN->name => Language::EN
        ];

        foreach ($cases as $country => $language) {
            $store = $this->convert(updates: ['countryCode' => $country]);
            $this->assertSame(
                expected: $language,
                actual: $store->getLanguage()
            );
        }
    }
}
