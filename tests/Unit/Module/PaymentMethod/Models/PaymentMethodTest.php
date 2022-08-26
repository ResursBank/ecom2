<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod\Status;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\EcomTest\Data\GetPaymentMethods;
use stdClass;
use ValueError;

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
     * @var PaymentMethod
     */
    private PaymentMethod $item;

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
        $this->data = GetPaymentMethods::getRandomPaymentMethodData();

        parent::setUp();
    }

    /**
     * @param array $updates
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
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
            type: PaymentMethod::class
        );

        if (!$item instanceof PaymentMethod) {
            throw new TestException(
                message: 'Conversion succeeded but did not return Method instance.'
            );
        }

        $this->item = $item;
    }

    /**
     * Assert validateId() raises Error when id is empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
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
     * @throws ReflectionException
     * @throws TestException
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
     * @throws ReflectionException
     * @throws TestException
     */
    public function testIdAssigned(): void
    {
        $this->convert();
        self::assertSame(expected: $this->data->id, actual: $this->item->id);
    }

    /**
     * Assert validateCustomerType() accepts value NATURAL.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateCustomerTypeAcceptsNatural(): void
    {
        $this->convert(updates: ['customerType' => 'NATURAL']);
        self::assertSame(expected: 'NATURAL', actual: $this->item->customerType);
    }

    /**
     * Assert validateCustomerType() accepts value LEGAL.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateCustomerTypeAcceptsLegal(): void
    {
        $this->convert(updates: ['customerType' => 'LEGAL']);
        self::assertSame(expected: 'LEGAL', actual: $this->item->customerType);
    }

    /**
     * Assert validateCustomerType() accepts empty value.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateCustomerTypeAcceptsEmpty(): void
    {
        $this->convert(updates: ['customerType' => '']);
        self::assertSame(expected: '', actual: $this->item->customerType);
    }

    /**
     * Assert validateCustomerType() throws IllegalValueException when
     * customerType is not one of its legal values.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateCustomerTypeThrowsWithInvalidValue(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['customerType' => 'natural']);
    }


    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testCustomerTypeWasAssigned(): void
    {
        if (!isset($this->data->customerType)) {
            $this->data->customerType = '';
        }

        $this->convert();
        self::assertSame(
            expected: $this->data->customerType,
            actual: $this->item->customerType
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testDisplayOrderWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->displayOrder,
            actual: $this->item->displayOrder
        );
    }

    /**
     * Assert validateDescription() throws EmptyValueException when description
     * is empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidatedDescriptionThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['description' => '']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testDescriptionWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->description,
            actual: $this->item->description
        );
    }

    /**
     * Assert validateValidFrom() throws EmptyValueException when validFrom is
     * empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidatedValidFromThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['validFrom' => '']);
    }

    /**
     * Assert validateValidFrom() throws IllegalValueException when validFrom is
     * not formatted as a date.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidatedValidFromThrowsWithoutDate(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['validFrom' => 'not-really-a-date']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidFromWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->validFrom,
            actual: $this->item->validFrom
        );
    }

    /**
     * Assert validateValidTo() throws EmptyValueException when validTo is
     * empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidatedValidToThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['validTo' => '']);
    }

    /**
     * Assert validateValidTo() throws IllegalValueException when validTo is
     * not formatted as a date.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidatedValidToThrowsWithoutDate(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['validTo' => '2018_44_1']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidToWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->validTo,
            actual: $this->item->validTo
        );
    }

    /**
     * Assert validateSupportedActions() accepts empty array.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateSupportedActionsAcceptsEmptyArray(): void
    {
        $this->convert(updates: ['supportedActions' => []]);
        self::assertSame(expected: [], actual: $this->item->supportedActions);
    }

    /**
     * Assert validateSupportedActions() throws IllegalValueException when the
     * array is not sequential.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateSupportedActionsThrowsWithAssoc(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['supportedActions' => [
            'some' => 'data'
        ]]);
    }

    /**
     * Assert validateSupportedActions() throws IllegalTypeException when the
     * array contains data types other than string.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateSupportedActionsThrowsWithIllegalKeyType(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->convert(updates: ['supportedActions' =>
            [55, 'asd', true]
        ]);
    }

    /**
     * Assert validateSupportedActions() throws ValueError when supplied value
     * is not defined by SupportedActions enum.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateSupportedActionsThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: ValueError::class);
        $this->convert(updates: ['supportedActions' =>
            ['DEBIT', 'CREDIT', 'CATALYST']
        ]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testSupportedActionsWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->supportedActions,
            actual: $this->item->supportedActions
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testMinPurchaseLimitWasAssigned(): void
    {
        $this->convert();
        self::assertEquals(
            expected: $this->data->minPurchaseLimit,
            actual: $this->item->minPurchaseLimit
        );
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testMaxPurchaseLimitWasAssigned(): void
    {
        $this->convert();
        self::assertEquals(
            expected: $this->data->maxPurchaseLimit,
            actual: $this->item->maxPurchaseLimit
        );
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
        $this->convert();
        self::assertEquals(
            expected: $this->data->minApplicationLimit,
            actual: $this->item->minApplicationLimit
        );
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
        $this->convert();
        self::assertEquals(
            expected: $this->data->maxApplicationLimit,
            actual: $this->item->maxApplicationLimit
        );
    }

    /**
     * Assert validateType() throws EmptyValueException when type is empty.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidatedTypeThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->convert(updates: ['type' => '']);
    }

    /**
     * Assert validateType() throws IllegalValueException when containing an
     * illegal character.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateTypeThrowsWithIllegalKeyChar(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->convert(updates: ['type' => 'WiERD']);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testTypeWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->type,
            actual: $this->item->type
        );
    }

    /**
     * Assert property was assigned during object conversion, and maintains its
     * data integrity.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testStatusWasAssigned(): void
    {
        $this->convert();
        self::assertInstanceOf(
            expected: Status::class,
            actual: $this->item->status
        );

        $status = $this->data->status;

        if (!$status instanceof stdClass) {
            throw new TestException(message: 'Invalid Method status property.');
        }

        self::assertSame(
            expected: $status->disabled,
            actual: $this->item->status->disabled
        );
        self::assertSame(
            expected: $status->disabledReasons,
            actual: $this->item->status->disabledReasons
        );
        self::assertSame(
            expected: $status->requireLimitRaise,
            actual: $this->item->status->requireLimitRaise
        );
    }
}
