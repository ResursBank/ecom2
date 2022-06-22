<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Lib\Validation;

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
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @psalm-suppress PropertyNotSetInConstructor
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
        self::assertSame(
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
        self::assertTrue(condition: $this->stringValidation->notEmpty(value: 'test'));
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
        self::assertTrue(
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
        self::assertTrue(
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
        self::assertTrue(
            condition: $this->stringValidation->isInt(value: '1234234456567789')
        );
    }

    /**
     * Assert isDate() throws IllegalValueException when the value isn't a date.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsDateThrows(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->stringValidation->isDate(value: 'not-a-date');
    }

    /**
     * Assert isDate() return TRUE when supplied a date.
     *
     * @return void
     * @throws IllegalValueException
     */
    public function testIsDateReturnsTrue(): void
    {
        self::assertTrue(
            condition: $this->stringValidation->isDate(value: '2007-07-12')
        );
    }
}
