<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Exception;
use Resursbank\Ecom\Lib\Utilities\Random\DataType;
use stdClass;

/**
 * Methods to randomize values of various data-types.
 */
class Random
{
    /**
     * Generates a random string of characters.
     *
     * @throws Exception
     */
    public static function getString(
        ?int $length = null
    ): string {
        $length ??= self::getInt(min: 0, max: 9999);

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
     * @todo Could be improved to populate object with random values and types.
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

        $size ??= self::getInt(min: 0, max: 999);

        for ($i = 0; $i < $size; $i++) {
            $result[] = match ($type) {
                DataType::ARRAY => self::getArray(),
                DataType::BOOL => self::getBool(),
                DataType::STRING => self::getString(),
                DataType::INT => self::getInt(),
                DataType::FLOAT => self::getFloat(),
                DataType::OBJECT => self::getObject()
            };
        }

        return $result;
    }
}
