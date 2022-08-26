<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models\Item;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\PaymentMethod\Enum\Status\DisabledReasons;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod\Status;
use Resursbank\EcomTest\Data\GetPaymentMethods;
use stdClass;
use ValueError;

/**
 * Test data integrity of Method\Status entity model.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    private Status $item;

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
        $data = GetPaymentMethods::getRandomPaymentMethodData();

        if (!isset($data->status)) {
            throw new TestException(message: 'Missing Method status property.');
        }

        if (!$data->status instanceof stdClass) {
            throw new TestException(message: 'Invalid Method status property.');
        }

        $this->data = $data->status;

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
            type: Status::class
        );

        if (!$item instanceof Status) {
            throw new TestException(
                message: 'Conversion succeeded but did not return Method instance.'
            );
        }

        $this->item = $item;
    }

    /**
     * Assert disabled property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testDisabledAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->disabled,
            actual: $this->item->disabled
        );
    }

    /**
     * Assert requireLimitRaise property was assigned during object conversion.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testRequireLimitRaiseAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->requireLimitRaise,
            actual: $this->item->requireLimitRaise
        );
    }

    /**
     * Assert validateDisabledReasons() accepts empty array.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateDisabledReasonsAcceptsEmptyArray(): void
    {
        $this->convert(updates: ['disabledReasons' => []]);
        self::assertSame(expected: [], actual: $this->item->disabledReasons);
    }

    /**
     * Assert validateDisabledReasons() throws IllegalValueException when the
     * array is not sequential.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateDisabledReasonsThrowsWithAssoc(): void
    {
        $this->expectException(exception: IllegalValueException::class);
        $this->convert(updates: ['disabledReasons' => [
            'some' => 'data'
        ]]);
    }

    /**
     * Assert validateDisabledReasons() throws IllegalTypeException when the
     * array contains data types other than string.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException
     */
    public function testValidateDisabledReasonsThrowsWithIllegalKeyType(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->convert(updates: ['disabledReasons' =>
            [55, 'asd', true]
        ]);
    }

    /**
     * Assert validateDisabledReasons() throws ValueError when supplied value
     * is not defined by DisabledReasons enum.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testValidateDisabledReasonsThrowsWithEmptyValue(): void
    {
        $this->expectException(exception: ValueError::class);
        $this->convert(updates: ['disabledReasons' =>
            ['AMOUNT_NOT_MATCHING', 'LAZY_LOADER']
        ]);
    }

    /**
     * Assert property was assigned during object conversion.
     *
     * @return void
     * @throws ReflectionException
     * @throws TestException|IllegalTypeException
     */
    public function testDisabledReasonsWasAssigned(): void
    {
        $this->convert();
        self::assertSame(
            expected: $this->data->disabledReasons,
            actual: $this->item->disabledReasons
        );
    }
}
