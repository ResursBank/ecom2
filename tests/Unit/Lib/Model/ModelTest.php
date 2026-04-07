<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Resursbank\Ecom\Lib\Attribute\Validation\FloatValue;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsIpAddress;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Callback\Enum\TestStatus;
use Resursbank\Ecom\Lib\Model\Callback\TestResponse;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\EcomTest\Data\Models\ArrayPropertyDummy;
use Resursbank\EcomTest\Data\Models\ObjectPropertyDummy;
use Resursbank\EcomTest\Data\Models\SimpleDummy;
use Resursbank\EcomTest\Data\Probe\Models\InvalidAttributeModel;
use Resursbank\EcomTest\Data\Probe\Models\Store;

/**
 * Verifies that the Model class works as intended.
 */
final class ModelTest extends TestCase
{
    /**
     * Verify that simple un-nested conversion to array works
     */
    public function testSimpleToArray(): void
    {
        $object = new SimpleDummy(number: 42, message: 'Foo');

        $expected = [
            'number' => 42,
            'message' => 'Foo',
        ];

        $this::assertSame(
            expected:$expected,
            actual: $object->toArray()
        );
    }

    /**
     * Verify that getValidationAttributes returns correct attributes.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testGetValidationAttributes(): void
    {
        $parameter = new ReflectionParameter(
            function: [Store::class, '__construct'],
            param: 3
        );

        $attributes = Model::getValidationAttributes(parameter: $parameter);

        $this->assertInstanceOf(
            expected: ArraySize::class,
            actual: $attributes[0]
        );
        $this->assertEquals(expected: 0, actual: $attributes[0]->min);
        $this->assertEquals(expected: 5, actual: $attributes[0]->max);
        $this->assertInstanceOf(
            expected: ArrayOfStrings::class,
            actual: $attributes[1]
        );
    }

    /**
     * Verify that isValidationAttribute correctly identifies attributes.
     *
     * @throws AttributeParameterException
     */
    public function testIsValidationAttribute(): void
    {
        $validTypes = [
            new ArrayOfStrings(),
            new ArraySize(min: 0),
            new CollectionSize(min: 0),
            new FloatValue(min: 0),
            new IntValue(min: 0),
            new StringIsDatetime(),
            new StringIsIpAddress(),
            new StringIsUrl(),
            new StringIsUuid(),
            new StringLength(min: 0),
            new StringMatchesRegex(pattern: '/\d+/'),
            new StringNotEmpty()
        ];

        foreach ($validTypes as $type) {
            $this->assertTrue(
                condition: Model::isValidationAttribute(attribute: $type)
            );
        }

        $this->assertFalse(
            condition: Model::isValidationAttribute(
                attribute: new TestResponse(
                    status: TestStatus::OK,
                    code: 200
                )
            )
        );
    }

    /**
     * Verify that validateAttributeCombos works as intended.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    // phpcs:ignore
    public function testValidateAttributeCombination(): void
    {
        $valid = new ReflectionParameter(
            function: [Store::class, '__construct'],
            param: 3
        );

        $combo = [];

        foreach ($valid->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if (!Model::isValidationAttribute(attribute: $instance)) {
                continue;
            }

            $combo[] = $instance;
        }

        try {
            Model::validateAttributeCombination(
                parameter: $valid,
                combo: $combo
            );
            $this->addToAssertionCount(count: 1);
        } catch (AttributeCombinationException) {
            $this->fail(message: 'Valid combination caused exception');
        }

        $invalid = new ReflectionParameter(
            function: [InvalidAttributeModel::class, '__construct'],
            param: 0
        );

        $combo = [];

        foreach ($invalid->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if (!Model::isValidationAttribute(attribute: $instance)) {
                continue;
            }

            $combo[] = $instance;
        }

        $this->expectException(AttributeCombinationException::class);

        Model::validateAttributeCombination(parameter: $invalid, combo: $combo);
    }

    /**
     * Verify that conversion to array works when object has object properties
     */
    public function testWithObjectPropertiesToArray(): void
    {
        $object = new ObjectPropertyDummy(
            object: new SimpleDummy(
                number: 42,
                message: 'Foo'
            ),
            message: 'bar'
        );

        $expected = [
            'object' => [
                'number' => 42,
                'message' => 'Foo',
            ],
            'message' => 'bar',
        ];

        $this::assertSame(
            expected: $expected,
            actual: $object->toArray()
        );
    }

    /**
     * Verify that conversion to array works when object has array properties
     */
    public function testWithArrayPropertiesToArray(): void
    {
        $object = new ArrayPropertyDummy(
            array: [
                'object' => new SimpleDummy(
                    number: 127,
                    message: 'Foo'
                ),
                'number' => 42,
            ],
            message: 'bar'
        );

        $expected = [
            'array' => [
                'object' => [
                    'number' => 127,
                    'message' => 'Foo',
                ],
                'number' => 42,
            ],
            'message' => 'bar',
        ];

        $this::assertEquals(
            expected: $expected,
            actual: $object->toArray(full: true)
        );
    }
}
