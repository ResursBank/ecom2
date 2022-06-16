<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use ReflectionClass;
use ReflectionObject;
use ReflectionNamedType;
use ReflectionException;

/**
 * Utility class for data type conversions
 */
class DataConverter
{
    /**
     * Converts stdClass objects to specified type
     *
     * @param object $object
     * @param class-string $type
     * @return object
     * @throws ReflectionException
     */
    public static function stdClassToType(object $object, string $type): object
    {
        $sourceReflection = new ReflectionObject(object: $object);
        $destReflection = new ReflectionClass(objectOrClass: $type);
        $sourceProperties = $sourceReflection->getProperties();
        $arguments = [];
        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(accessible: true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue($object);

            if ($destReflection->hasProperty($name)) {
                if (is_object($value)) {
                    $destinationProperty = $destReflection->getProperty($name);

                    /** @var ReflectionNamedType $destinationType */
                    $destinationType = $destinationProperty->getType();
                    $propertyType = $destinationType->getName();
                    $arguments[$name] = self::stdClassToType(object: $value, type: $propertyType);
                } else {
                    $arguments[$name] = $value;
                }
            }
        }

        return new $type(...$arguments);
    }
}
