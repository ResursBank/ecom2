<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Model\Item;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\PaymentMethod\Model\Item\Status;
use Resursbank\EcomTest\Data\ApiResponse\GetPaymentMethods;
use stdClass;

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
    private Status $status;

    /**
     * @var stdClass
     */
    private stdClass $data;

    /**
     * @return void
     * @throws JsonException
     * @throws TestException
     * @throws ReflectionException
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

        $status = DataConverter::stdClassToType(
            object: $this->data,
            type: Status::class
        );

        if (!$status instanceof Status) {
            throw new TestException(
                message: 'Conversion succeeded but did not return Method instance.'
            );
        }

        $this->status = $status;

        parent::setUp();
    }

    /**
     * Assert disabled property was assigned during object conversion.
     *
     * @return void
     */
    public function testDisabledAssigned(): void
    {
        self::assertSame(
            expected: $this->data->disabled,
            actual: $this->status->disabled
        );
    }

    /**
     * Assert requireLimitRaise property was assigned during object conversion.
     *
     * @return void
     */
    public function testRequireLimitRaiseAssigned(): void
    {
        self::assertSame(
            expected: $this->data->requireLimitRaise,
            actual: $this->status->requireLimitRaise
        );
    }

    /**
     * Assert disabledReasons property was assigned during object conversion.
     *
     * @return void
     */
    public function testDisabledReasonsAssigned(): void
    {
        self::assertSame(
            expected: $this->data->disabledReasons,
            actual: $this->status->disabledReasons
        );
    }
}
