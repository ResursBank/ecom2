<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use ArgumentCountError;
use BackedEnum;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionObject;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\Model;
use stdClass;

use function call_user_func;
use function is_object;

/**
 * Utility class for data type conversions.
 */
class DataConverter
{
    /**
     * Converts stdClass objects to specified type.
     *
     * NOTE: The intention is that the conversion class itself validates
     * assigned values through its constructor.
     *
     * @param class-string $type
     * @throws ReflectionException
     * @throws ArgumentCountError
     * @throws IllegalTypeException|IllegalValueException
     */
    public static function stdClassToType(object $object, string $type): Model
    {
        if (!is_subclass_of(object_or_class: $type, class: Model::class)) {
            throw new IllegalValueException(message: "$type is not a Model");
        }

        $sourceReflection = new ReflectionObject(object: $object);
        $destReflection = new ReflectionClass(objectOrClass: $type);
        $sourceProperties = $sourceReflection->getProperties();
        $arguments = [];

        foreach ($sourceProperties as $sourceProperty) {
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue(object: $object);

            if (!$destReflection->hasProperty(name: $name)) {
                continue;
            }

            $destinationProperty = $destReflection->getProperty(name: $name);
            /** @var ReflectionNamedType $destinationType */
            $destinationType = $destinationProperty->getType();
            $propertyType = $destinationType->getName();

            $arguments[$name] = self::processProperty(
                value: $value,
                propertyType: $propertyType
            );
        }

        return new $type(...$arguments);
    }

    /**
     * @param class-string $type
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    public static function arrayToCollection(array $data, string $type): Collection
    {
        if (!is_subclass_of(object_or_class: $type, class: Model::class)) {
            throw new IllegalValueException(message: "$type is not a Model");
        }

        $convertedData = [];

        foreach ($data as $item) {
            $convertedData[] = self::stdClassToType(object: $item, type: $type);
        }

        $class = $type . 'Collection';

        if (
            !is_subclass_of(object_or_class: $class, class: Collection::class)
        ) {
            throw new IllegalValueException(
                message: "$type is not a Collection"
            );
        }

        return new $class(data: $convertedData);
    }

    /**
     * Process individual property.
     *
     * @param mixed $value Property value
     * @param string $propertyType Property type
     * @return mixed Converted property
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    private static function processProperty(mixed $value, string $propertyType): mixed
    {
        // If our property is a collection we need to take the value array and convert all items individually
        // before loading our new collection object
        if (
            is_subclass_of(
                object_or_class: $propertyType,
                class: Collection::class
            )
        ) {
             return self::processCollection(
                 value: $value,
                 propertyType: $propertyType
             );
        }

        if (
            $propertyType === 'array' &&
            $value instanceof stdClass &&
            empty((array)$value)
        ) {
            return [];
        }

        if (enum_exists(enum: $propertyType)) {
            return self::processEnum(
                value: $value,
                propertyType: $propertyType
            );
        }

        if (is_object(value: $value)) {
            return self::stdClassToType(
                object: $value,
                /* @phpstan-ignore-next-line */
                type: $propertyType
            );
        }

        return $value;
    }

    /**
     * Process enum properties.
     *
     * If our property is an enum we need to convert the value to the enum
     * value it represents.
     *
     * @param mixed $value Property value
     * @param string $propertyType Property type
     * @return mixed Converted property or null
     */
    private static function processEnum(
        mixed $value,
        string $propertyType
    ): mixed {
        return $value !== null ?
            call_user_func(
            /* @phpstan-ignore-next-line */
                $propertyType . '::from',
                $value instanceof BackedEnum ? $value->value : $value
            ) : null;
    }

    /**
     * Process Collection (or child class) properties.
     *
     * @param mixed $value Property value
     * @param string $propertyType Property type
     * @return Collection Converted property.
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    private static function processCollection(
        mixed $value,
        string $propertyType
    ): Collection {
        $converted = [];
        $collection = new $propertyType(data: []);

        if (!$collection instanceof Collection) {
            throw new IllegalTypeException(
                message: $propertyType . ' is not a Collection.'
            );
        }

        $collectionType = $collection->getType();

        if (is_iterable(value: $value)) {
            $converted = self::getCollectionItems(
                items: $value,
                collectionType: $collectionType
            );
        }

        $collection->setData(data: $converted);
        return $collection;
    }

    /**
     * Convert individual collection items.
     *
     * @param iterable<object> $items Items to convert.
     * @param class-string $collectionType Collection type.
     * @return array Array of correctly typed items.
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    private static function getCollectionItems(
        iterable $items,
        string $collectionType
    ): array {
        $converted = [];

        foreach ($items as $item) {
            $converted[] = self::processCollectionItem(
                item: $item,
                collectionType: $collectionType
            );
        }

        return $converted;
    }

    /**
     * Convert single collection item.
     *
     * @param object $item Item to convert
     * @param class-string $collectionType Collection type
     * @return object Converted item.
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    private static function processCollectionItem(
        object $item,
        string $collectionType
    ): object {
        return self::stdClassToType(object: $item, type: $collectionType);
    }
}
