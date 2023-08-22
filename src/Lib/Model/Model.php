<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use BackedEnum;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
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
    public function __construct()
    {
        $this->validateProperties();
    }

    /**
     * Converts the object to an array suitable for use with the Curl library.
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @todo Refactor see ECP-354. Remove phpcs:ignore when done.
     */
    // phpcs:ignore
    public function toArray(
        bool $full = false,
        ?array $raw = null
    ): array {
        $data = [];

        $raw ??= get_object_vars(object: $this);

        foreach ($raw as $name => $value) {
            if (is_object(value: $value)) {
                // Skip DI.
                if ($value instanceof Collection || $value instanceof self) {
                    $data[$name] = $value->toArray(full: $full);
                }

                if ($value instanceof BackedEnum) {
                    $data[$name] = $value->value;
                }
            } elseif (is_array(value: $value)) {
                // Support arrays containing Model|Collection.
                $data[$name] = $this->toArray(full: $full, raw: $value);
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Validate object properties.
     *
     * @throws ReflectionException
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
     */
    private function validateProperty(ReflectionParameter $parameter): void
    {
        if ($this->{$parameter->name} === null && $parameter->allowsNull()) {
            return;
        }

        $attributes = $parameter->getAttributes();

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            if (
                !method_exists(object_or_class: $instance, method: 'validate')
            ) {
                return;
            }

            /* Complains about stdClass even though object is never of that type */
            $instance->validate(
            /* @phpstan-ignore-next-line */
                name: $parameter->name,
                /* @phpstan-ignore-next-line */
                value: $this->{$parameter->name}
            );
        }
    }
}
