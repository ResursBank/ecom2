<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Repository\Traits;

use Exception;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethodCollection;
use stdClass;
use Resursbank\EcomTest\Data\GetPaymentMethods;

/**
 * Verifies business logic of ModelConverter trait.
 *
 *  @psalm-suppress PropertyNotSetInConstructor
 */
final class ModelConverterTest extends TestCase
{
    use ModelConverter;

    /**
     * Assert validateModel() throws InvalidArgumentException when supplied a
     * value which is not a class.
     *
     * @return void
     * @throws IllegalTypeException
     */
    public function testValidateModelThrowsWithoutClass(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);

        /** @spalm-suppress ArgumentTypeCoercion|UndefinedClass */
        $this->validateModel(model: 'ThereIsNoSpoon');
    }

    /**
     * Assert validateModel() throws IllegalTypeException when supplied a class
     * that is not a subclass of Model.
     *
     * @return void
     * @throws IllegalTypeException
     */
    public function testValidateThrowsWithoutModelClass(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->validateModel(model: Exception::class);
    }

    /**
     * Assert convertToModel() throws InvalidArgumentException when supplied a
     * model class that does not exist.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testConvertToModelThrowsWithoutClass(): void
    {
        $this->expectException(exception: InvalidArgumentException::class);

        /** @spalm-suppress ArgumentTypeCoercion | UndefinedClass */
        $this->convertToModel(data: new stdClass(), model: 'Witch');
    }

    /**
     * Assert convertToModel() throws InvalidArgumentException when supplied a
     * model class that does not exist.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testConvertToModelThrowsWithoutModelClass(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->convertToModel(data: new stdClass(), model: Exception::class);
    }

    /**
     * Assert convertToModel() converts JSON to request Model instance.
     *
     * @throws IllegalTypeException
     * @throws TestException
     * @throws ReflectionException
     * @throws JsonException
     */
    public function testConvertToModelConvertsJsonModel(): void
    {
        $result = $this->convertToModel(
            data: json_encode(
                value: GetPaymentMethods::getRandomPaymentMethodData(),
                flags: JSON_THROW_ON_ERROR
            ),
            model: PaymentMethod::class,
        );

        self::assertInstanceOf(expected: PaymentMethod::class, actual: $result);
    }

    /**
     * Assert convertToModel() converts JSON to request Model instance.
     *
     * @throws IllegalTypeException
     * @throws TestException
     * @throws ReflectionException
     * @throws JsonException
     */
    public function testConvertToModelConvertsJsonArray(): void
    {
        $result = $this->convertToModel(
            data: json_encode(
                value: [
                    GetPaymentMethods::getRandomPaymentMethodData(),
                    GetPaymentMethods::getRandomPaymentMethodData(),
                    GetPaymentMethods::getRandomPaymentMethodData(),
                    GetPaymentMethods::getRandomPaymentMethodData(),
                    GetPaymentMethods::getRandomPaymentMethodData()
                ],
                flags: JSON_THROW_ON_ERROR
            ),
            model: PaymentMethod::class,
        );

        self::assertInstanceOf(
            expected: PaymentMethodCollection::class,
            actual: $result
        );
    }

    /**
     * Assert convertToModel() converts stdClass instance to Model instance.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testConvertToModelConvertsStdclass(): void
    {
        $result = $this->convertToModel(
            data: GetPaymentMethods::getRandomPaymentMethodData(),
            model: PaymentMethod::class,
        );

        self::assertInstanceOf(
            expected: PaymentMethod::class,
            actual: $result
        );
    }

    /**
     * Assert convertToModel() converts array of stdClass instances to Model
     * instances.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TestException
     */
    public function testConvertToModelConvertsStdclassArray(): void
    {
        $result = $this->convertToModel(
            data: [
                GetPaymentMethods::getRandomPaymentMethodData(),
                GetPaymentMethods::getRandomPaymentMethodData(),
                GetPaymentMethods::getRandomPaymentMethodData(),
                GetPaymentMethods::getRandomPaymentMethodData(),
                GetPaymentMethods::getRandomPaymentMethodData()
            ],
            model: PaymentMethod::class,
        );

        self::assertInstanceOf(
            expected: PaymentMethodCollection::class,
            actual: $result
        );
    }
}
