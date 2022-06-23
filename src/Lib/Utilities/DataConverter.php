<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use ArgumentCountError;
use ReflectionClass;
use ReflectionObject;
use ReflectionNamedType;
use ReflectionException;

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
     * @param object $object
     * @param class-string $type
     * @return mixed
     * @throws ReflectionException
     * @throws ArgumentCountError
     * @psalm-suppress MixedAssignment
     * @psalm-suppress InvalidNamedArgument
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress MixedMethodCall
     */
    public static function stdClassToType(object $object, string $type): mixed
    {
        $sourceReflection = new ReflectionObject(object: $object);
        $destReflection = new ReflectionClass(objectOrClass: $type);
        $sourceProperties = $sourceReflection->getProperties();
        $arguments = [];
        foreach ($sourceProperties as $sourceProperty) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            $sourceProperty->setAccessible(accessible: true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue(object: $object);

            if ($destReflection->hasProperty(name: $name)) {
                if (is_object(value: $value)) {
                    $destinationProperty = $destReflection->getProperty(
                        name: $name
                    );

                    /** @var ReflectionNamedType $destinationType */
                    $destinationType = $destinationProperty->getType();
                    $propertyType = $destinationType->getName();
                    $arguments[$name] = self::stdClassToType(
                        object: $value,
                        type: $propertyType
                    );
                } else {
                    $arguments[$name] = $value;
                }
            }
        }

        return new $type(...$arguments);
    }
}
