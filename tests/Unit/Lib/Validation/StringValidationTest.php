<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Validation;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Test string validation methods.
 */
final class StringValidationTest extends TestCase
{
    /**
     * @var StringValidation
     */
    private StringValidation $stringValidation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->stringValidation = new StringValidation();

        parent::setUp();
    }

    /**
     * Assert getKey() throws MissingKeyException when the needle does not
     * exist.
     *
     * @return void
     * @throws ValidationException
     */
    public function testGetKeyThrowsWithMissing(): void
    {
        $this->expectException(exception: MissingKeyException::class);
        $this->stringValidation->getKey(data: ['type', '55'], key: 'test');
    }

    /**
     * Assert getKey() throws IllegalTypeException when the needle exists but
     * is not a string.
     *
     * @return void
     * @throws ValidationException
     */
    public function testGetKeyThrowsWithInvalidProperty(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->stringValidation->getKey(data: ['hum' => 1], key: 'hum');
    }

    /**
     * Assert getKey() returns resolved key.
     *
     * @return void
     * @throws ValidationException
     */
    public function testGetKeyReturnsTrue(): void
    {
        $this->assertSame(
            expected: 'thatValue',
            actual: $this->stringValidation->getKey(
                data: ['thisKey' => 'thatValue'],
                key: 'thisKey'
            )
        );
    }

    /**
     * Assert notEmpty() throws EmptyValueException when supplied an empty
     * string.
     *
     * @return void
     * @throws ValidationException
     */
    public function testNotEmptyThrowsWithEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->stringValidation->notEmpty(value: '');
    }

    /**
     * Assert notEmpty() throws EmptyValueException when supplied a string
     * containing only spaces.
     *
     * @return void
     * @throws ValidationException
     */
    public function testNotEmptyThrowsWithSpaces(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->stringValidation->notEmpty(value: '  ');
    }

    /**
     * Assert notEmpty() throws EmptyValueException when supplied a string
     * containing only newline.
     *
     * @return void
     * @throws ValidationException
     */
    public function testNotEmptyThrowsWithNewLine(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->stringValidation->notEmpty(value: "\n\n\n");
    }

    /**
     * Assert notEmpty() throws EmptyValueException when supplied an empty
     * string.
     *
     * @return void
     * @throws ValidationException
     */
    public function testNotEmptyReturnsTrue(): void
    {
        $this->assertTrue(condition: $this->stringValidation->notEmpty(value: 'test'));
    }

    /**
     * Assert matchRegex() throws IllegalCharsetException when supplied a
     * value containing an illegal character against the supplied pattern.
     *
     * @return void
     * @throws ValidationException
     */
    public function testMatchRegexThrowsOnIllegal(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->stringValidation->matchRegex(value: 'Some', pattern: '/^[a-z]+$/');
    }

    /**
     * Assert matchRegex() throws IllegalCharsetException when supplied a
     * value containing an illegal character against the supplied pattern.
     *
     * @return void
     * @throws ValidationException
     */
    public function testMatchRegexReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->stringValidation->matchRegex(
                value: 'Hello World',
                pattern: '/^[a-z\s]+$/i'
            )
        );
    }

    /**
     * Assert oneOf() throws InvalidValueException without a match.
     *
     * @return void
     * @throws ValidationException
     */
    public function testOneOfThrowsWithoutMatch(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->oneOf(value: 'some', set: ['Some', 'SOME']);
    }

    /**
     * Assert oneOf() returns TRUE with a match.
     *
     * @return void
     * @throws ValidationException
     */
    public function testOneOfReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->stringValidation->oneOf(
                value: 'some',
                set: ['Some', 'SOME', 'some']
            )
        );
    }

    /**
     * Assert isInt() throws IllegalCharsetException when supplied a value that
     * cannot be cast as an int.
     *
     * @return void
     * @throws ValidationException
     */
    public function testIsIntThrowsWithAlpha(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->stringValidation->isInt(value: '5.5');
    }

    /**
     * Assert isInt() return TRUE when value can be cast as an int.
     *
     * @return void
     * @throws ValidationException
     */
    public function testIsIntReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->stringValidation->isInt(value: '1234234456567789')
        );
    }

    /**
     * Assert length() return TRUE when supplied a string within length.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testLengthReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->stringValidation->length(
                value: '',
                min: 0,
                max: 5
            )
        );
        $this->assertTrue(
            condition: $this->stringValidation->length(
                value: '123',
                min: 0,
                max: 5
            )
        );
        $this->assertTrue(
            condition: $this->stringValidation->length(
                value: '12345',
                min: 0,
                max: 5
            )
        );
    }

    /**
     * Assert length() throws IllegalValueException when the string is too
     * short.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testLengthThrowsWhenTooShort(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->length(value: '1', min: 2, max: 5);
    }

    /**
     * Assert length() throws IllegalValueException when the string is too long.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testLengthThrowsWhenTooLong(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->length(value: '123456', min: 2, max: 5);
    }

    /**
     * Assert length() throws IllegalValueException when given a negative
     * minimum value.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testLengthThrowsWithNegativeMin(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->length(value: '123', min: -1, max: 5);
    }

    /**
     * Assert length() throws IllegalValueException when given a maximum value
     * that is less than the minimum.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testLengthThrowsWithInvalidMax(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->length(value: '123', min: 2, max: 0);
    }

    /**
     * Assert isUuid() throws IllegalValueException when the value isn't an
     * uuid.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsUuidThrowsIllegalValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->isUuid(value: 'not-a-uuid');
    }

    /**
     * Assert isUuid() return TRUE when supplied a uuid.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsUuidReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->stringValidation->isUuid(
                value: 'f81d4fae-7dec-11d0-a765-00a0c91e6bf6'
            )
        );
    }

    /**
     * Assert that isEmail() returns true when supplied a string with an @ sign in it
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsEmailReturnsTrue(): void
    {
        $this->assertTrue(
            condition: $this->stringValidation->isEmail(value: 'foo@example.com')
        );
    }

    /**
     * Assert that isEmail() throws an IllegalValueException when supplied with a string without an @ sign in it
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsEmailThrowsIllegalValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->isEmail(value: 'foobar');
    }

    /**
     * Assert that isTimestampDate accepts various values.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsTimestampConvertable(): void
    {
        $date = '2022-10-11T10:12:15';
        $date2 = '2022-10-11T10:12:15+01:10';
        $date3 = '2022-10-11T10:12:15.1';
        $date4 = '2022-10-11T10:12:15.123';
        $date5 = '2022-10-11T10:12:15.123123123';

        $this->assertNotFalse(
            condition: $this->stringValidation->isTimestampDate(value: $date),
            message: "$date is not timestamp compatible."
        );

        $this->assertNotFalse(
            condition: $this->stringValidation->isTimestampDate(value: $date2),
            message: "$date2 is not timestamp compatible."
        );

        $this->assertNotFalse(
            condition: $this->stringValidation->isTimestampDate(value: $date3),
            message: "$date3 is not timestamp compatible."
        );

        $this->assertNotFalse(
            condition: $this->stringValidation->isTimestampDate(value: $date4),
            message: "$date4 is not timestamp compatible."
        );

        $this->assertNotFalse(
            condition: $this->stringValidation->isTimestampDate(value: $date5),
            message: "$date5 is not timestamp compatible."
        );
    }

    /**
     * Assert isTimestampDate throws IllegalValueException when supplied a
     * string that can not be converted to a timestamp.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsTimestampConvertableThrows(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->isTimestampDate(value: '{"sneaky": "object"}');
    }
}
