<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use BackedEnum;
use JsonException;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\ArrayInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\CollectionInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\FloatInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\IntInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\StringInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsIpAddress;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Collection\Collection;

use function is_array;
use function is_object;

/**
 * Defines the basic structure of an Ecom model.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Model
{
    /**
     * List of valid validation attribute combinations.
     *
     * @var array<array>
     */
    private static array $attributeCombos = [
        [
            ArraySize::class,
            ArrayOfStrings::class
        ],
        [
            StringNotEmpty::class,
            StringIsUuid::class
        ],
        [
            StringNotEmpty::class,
            StringIsUrl::class
        ],
        [
            StringNotEmpty::class,
            StringIsDatetime::class
        ]
    ];

    /**
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    public function __construct()
    {
        $this->validateProperties();
    }

    /**
     * Get attributes utilised for validation attached to Model property.
     *
     * @throws JsonException
     * @throws AttributeCombinationException
     */
    public static function getValidationAttributes(
        ReflectionParameter $parameter
    ): array {
        $result = [];
        $combo = [];

        foreach ($parameter->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if (!self::isValidationAttribute(attribute: $instance)) {
                continue;
            }

            $result[] = $instance;
            $combo[] = $instance::class;
        }

        if (count($result) === 2) {
            self::validateAttributeCombination(
                parameter: $parameter,
                combo: $combo
            );
        }

        return $result;
    }

    /**
     * Check whether supplied $attribute is part of validation suite.
     */
    public static function isValidationAttribute(
        object $attribute
    ): bool {
        return
            // Adding this because I'm just trying to fix the tests and don't want to get bogged down in writing the
            // methods for test data.
            $attribute instanceof StringMatchesRegex ||
            $attribute instanceof StringIsIpAddress ||
            $attribute instanceof StringInterface ||
            $attribute instanceof IntInterface ||
            $attribute instanceof FloatInterface ||
            $attribute instanceof CollectionInterface ||
            $attribute instanceof ArrayInterface
        ;
    }

    /**
     * Confirm combination of validation attributes is functional.
     *
     * A combination of validation attributes requires one of the attributes to
     * implement business logic for producing testable data matching said
     * combination. Without that, automated tests cannot be safely conducted
     * and as such we will reject any combination we do not explicitly allow.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    // phpcs:ignore
    public static function validateAttributeCombination(
        ReflectionParameter $parameter,
        array $combo
    ): void {
        $comboAsStrings = [];

        foreach ($combo as $comboItem) {
            // "if (!is_string($comboItem)"?
            if (gettype($comboItem) === 'string') {
                $comboAsStrings[] = $comboItem;
                continue;
            }

            $comboAsStrings[] = $comboItem::class;
        }

        sort($comboAsStrings);

        $validCombo = false;

        foreach (self::$attributeCombos as $c) {
            sort($c);
            $validCombo = $c === $comboAsStrings;

            if ($validCombo) {
                break;
            }
        }

        if (!$validCombo) {
            throw new AttributeCombinationException(
                message: sprintf(
                    'Cannot combine %s attributes for parameter %s on %s',
                    json_encode(value: $combo, flags: JSON_THROW_ON_ERROR),
                    $parameter->name,
                    $parameter->getDeclaringClass()?->name
                )
            );
        }
    }

    /**
     * Converts the object to an array suitable for use with the Curl library.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function toArray(
        bool $full = false,
        ?array $raw = null
    ): array {
        $data = [];

        $raw ??= get_object_vars(object: $this);

        foreach ($raw as $name => $value) {
            $data[$name] = $this->propertyToArrayElement(
                value: $value,
                full: $full
            );
        }

        return $data;
    }

    /**
     * Convert property to array element.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function propertyToArrayElement(
        mixed $value,
        bool $full = false
    ): mixed {
        if (is_object(value: $value)) {
            return $this->objectToArrayElement(value: $value, full: $full);
        }

        if (is_array(value: $value)) {
            // Support arrays containing Model|Collection.
            return $this->toArray(full: $full, raw: $value);
        }

        return $value;
    }

    /**
     * Convert object to array element.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function objectToArrayElement(
        object $value,
        bool $full = false
    ): mixed {
        if ($value instanceof Collection || $value instanceof self) {
            return $value->toArray(full: $full);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return null;
    }

    /**
     * Validate object properties.
     *
     * @throws ReflectionException
     * @throws JsonException
     * @throws AttributeCombinationException
     */
    private function validateProperties(): void
    {
        $parameters = (new ReflectionMethod(
            objectOrMethod: $this,
            method: '__construct'
        ))->getParameters();

        foreach ($parameters as $parameter) {
            $this->validateProperty(parameter: $parameter);
        }
    }

    /**
     * Validate individual parameter.
     *
     * @throws JsonException
     * @throws AttributeCombinationException
     * @throws ReflectionException
     */
    private function validateProperty(ReflectionParameter $parameter): void
    {
        $property = new ReflectionProperty(
            class: $this::class,
            property: $parameter->getName()
        );

        if ($property->isPrivate()) {
            // We can't look at private properties so if we encounter one we
            // skip attempting to validate it.
            return;
        }

        if ($this->{$parameter->name} === null && $parameter->allowsNull()) {
            return;
        }

        foreach (
            self::getValidationAttributes(parameter: $parameter) as $attribute
        ) {
            $attribute->validate(
                name: $parameter->name,
                value: $this->{$parameter->name}
            );
        }
    }
}
