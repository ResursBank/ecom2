<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Enum\Type;

/**
 * Test data integrity of payment method entity model.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethodTest extends TestCase
{
    /**
     * @var array
     */
    private static array $data = [
        'id' => '4fcf7608-59df-4c4b-b49d-11063c58be7a',
        'name' => 'Faktura',
        'minPurchaseLimit' => 0.0,
        'maxPurchaseLimit' => 1000.0,
        'minApplicationLimit' => 0,
        'maxApplicationLimit' => 5000,
        'type' => 'RESURS_INVOICE',
        'legalLinks' => [],
        'enabledForLegalCustomer' => true,
        'enabledForNaturalCustomer' => true
    ];

    /**
     * @param array $updates
     * @return PaymentMethod
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    private function convert(
        array $updates = []
    ): PaymentMethod {
        $result = DataConverter::stdClassToType(
            object: (object) array_merge(self::$data, $updates),
            type: PaymentMethod::class
        );

        if (!$result instanceof PaymentMethod) {
            throw new TestException(
                message: 'Failed to convert stdClass to PaymentMethod.'
            );
        }

        return $result;
    }

    /**
     * Assert validateId() raises Error when id is empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testValidateIdThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['id' => '']);
    }

    /**
     * Assert validateId() throws IllegalValueException when not a UUID.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testValidateIdThrowsWithoutUuid(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['id' => 'Test5']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testIdAssigned(): void
    {
        $item = $this->convert();
        self::assertSame(expected: self::$data['id'], actual: $item->id);
    }

    /**
     * Assert validateName() throws EmptyValueException when name
     * is empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testValidatedNameThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['name' => '']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testNameWasAssigned(): void
    {
        $item = $this->convert();
        self::assertSame(
            expected: self::$data['name'],
            actual: $item->name
        );
    }

    /**
     * Assert validateMinPurchaseLimit() throws IllegalTypeException when
     * supplied a negative value.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testValidateMinPurchaseLimitThrowsWithNegativeValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['minPurchaseLimit' => -1]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testMinPurchaseLimitWasAssigned(): void
    {
        $item = $this->convert();
        self::assertEquals(
            expected: self::$data['minPurchaseLimit'],
            actual: $item->minPurchaseLimit
        );
    }

    /**
     * Assert validateMaxPurchaseLimit() throws IllegalTypeException when
     * supplied a negative value.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testValidateMaxPurchaseLimitThrowsWithNegativeValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['maxPurchaseLimit' => -1]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testMaxPurchaseLimitWasAssigned(): void
    {
        $item = $this->convert();
        self::assertEquals(
            expected: self::$data['maxPurchaseLimit'],
            actual: $item->maxPurchaseLimit
        );
    }

    /**
     * Assert validateMinApplicationLimit() throws IllegalTypeException when
     * supplied a negative value.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateMinApplicationLimitThrowsWithNegativeValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['minApplicationLimit' => -1]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testMinApplicationLimitWasAssigned(): void
    {
        $item = $this->convert();
        self::assertEquals(
            expected: self::$data['minApplicationLimit'],
            actual: $item->minApplicationLimit
        );
    }

    /**
     * Assert validateMaxApplicationLimit() throws IllegalTypeException when
     * supplied a negative value.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateMaxApplicationLimitThrowsWithNegativeValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['maxApplicationLimit' => -1]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testMaxApplicationLimitWasAssigned(): void
    {
        $item = $this->convert();
        self::assertEquals(
            expected: self::$data['maxApplicationLimit'],
            actual: $item->maxApplicationLimit
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testLegalLinksWasAssigned(): void
    {
        $item = $this->convert();
        self::assertSame(
            expected: self::$data['legalLinks'],
            actual: $item->legalLinks
        );
    }


    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException|TestException
     */
    public function testTypeAssigned(): void
    {
        $item = $this->convert();
        self::assertSame(
            expected: Type::RESURS_INVOICE,
            actual: $item->type
        );
    }
}
