<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Exception;
use ReflectionParameter;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\Random\DataType;
use stdClass;

use function strlen;

/**
 * Methods to randomize values of various data-types.
 */
class Random
{
    /**
     * @throws Exception
     */
    public static function getTypeValue(
        DataType $type
    ): mixed {
        return match ($type) {
            DataType::STRING => self::getString(),
            DataType::INT => self::getInt(),
            DataType::FLOAT => self::getFloat(),
            DataType::BOOL => self::getBool(),
            DataType::OBJECT => self::getObject(),
            DataType::ARRAY => self::getArray()
        };
    }

    /**
     * Get random DataType case.
     */
    public static function getType(): DataType
    {
        $cases = DataType::cases();

        /* @phpstan-ignore-next-line */
        return $cases[array_rand(array: $cases)];
    }

    /**
     * Get random value of random type specified in DataType.
     *
     * @throws Exception
     */
    public static function getValue(): mixed
    {
        return self::getTypeValue(
            type: self::getType()
        );
    }

    /**
     * Resolve default value based on datatype.
     *
     * @throws Exception
     */
    public static function getParameterValue(
        ReflectionParameter $parameter
    ): mixed {
        if (!$parameter->hasType()) {
            return 0;
        }

        $type = (string) $parameter->getType();

        if (str_contains(haystack: $type, needle: '|')) {
            $type = substr(
                string: $type,
                offset: 0,
                length: (int) strpos(haystack: $type, needle: '|')
            );
        }

        return self::getTypeValue(
            type: DataType::from(value: strtoupper(string: $type))
        );
    }

    /**
     * Generates a random string of characters.
     *
     * @throws Exception
     */
    public static function getString(
        ?int $length = null,
        ?array $characters = null
    ): string {
        $length ??= self::getInt(min: 0, max: 9999);

        if (!empty($characters)) {
            return self::getStringWithCharset(
                length: $length,
                charset: $characters
            );
        }

        return substr(
            string: bin2hex(
                string: random_bytes(length: max(1, $length))
            ),
            offset: 0,
            length: $length
        );
    }

    /**
     * @throws Exception
     */
    public static function getInt(
        ?int $min = null,
        ?int $max = null
    ): int {
        $min ??= random_int(min: 0, max: 99999999);
        $max ??= random_int(min: $min, max: 999999999);

        return random_int(min: $min, max: $max);
    }

    /**
     * @throws Exception
     */
    public static function getFloat(
        ?int $min = null,
        ?int $max = null
    ): float {
        $int = self::getInt(min: $min, max: $max);
        $decimal = self::getInt(min: 1, max: 99) / 100;

        return $int + $decimal;
    }

    /**
     * @throws Exception
     */
    public static function getBool(): bool
    {
        return (bool) self::getInt(min: 0, max: 1);
    }

    /**
     * @return stdClass
     */
    public static function getObject(): object
    {
        return new stdClass();
    }

    /**
     * @throws Exception
     */
    public static function getArray(
        ?int $size = null,
        DataType $type = DataType::STRING
    ): array {
        $result = [];

        $size ??= self::getInt(min: 0, max: 99);

        for ($i = 0; $i < $size; $i++) {
            $result[] = self::getTypeValue(type: $type);
        }

        return $result;
    }

    /**
     * Get random string made up of characters from specified array.
     *
     * @param array $charset
     * @throws IllegalValueException
     */
    private static function getStringWithCharset(
        int $length,
        array $charset
    ): string {
        $string = '';

        foreach ($charset as $char) {
            if (!is_string(value: $char) || strlen(string: $char) !== 1) {
                throw new IllegalValueException(message: 'Element \'' . $char .
                    '\'in character array is not a single-character string');
            }
        }

        while (strlen(string: $string) < $length) {
            $string .= $charset[array_rand($charset)];
        }

        return $string;
    }
}
