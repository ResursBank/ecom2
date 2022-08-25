<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data;

use JsonException;
use Resursbank\Ecom\Exception\TestException;
use stdClass;

use function is_array;
use function is_int;

/**
 * Mock data for tests relating to Store module.
 *
 * @todo Add more data to this class.
 */
class OrderLine
{
    /**
     * @var string
     */
    public static string $data = <<<EOD
[
    {
        "description": "Bok",
        "quantity": 2,
        "reference": "T-800",
        "type": "PHYSICAL_GOODS",
        "quantityUnit": "st",
        "unitAmountIncludingVat": 150.75,
        "vatRate": 25,
        "totalAmountIncludingVat": 301.5,
        "totalVatAmount": 60.3
    },
    {
        "description": "Album",
        "quantity": 1,
        "reference": "ALBUM-012G-VV",
        "type": "DIGITAL_GOODS",
        "quantityUnit": "st",
        "unitAmountIncludingVat": 120.0,
        "vatRate": 25,
        "totalAmountIncludingVat": 199.9,
        "totalVatAmount": 79.9
    }
]
EOD;

    /**
     * @return stdClass
     * @throws JsonException
     * @throws TestException
     */
    public static function getRandomData(): stdClass
    {
        $data = json_decode(
            json: self::$data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        if (!is_array(value: $data)) {
            throw new TestException(
                message: 'Failed to decode JSON data to array.'
            );
        }

        /** @phpstan-ignore-next-line */
        $randIndex = array_rand(array: $data);

        if (!is_int(value: $randIndex)) {
            throw new TestException(
                message: 'Failed to resolve random data index.'
            );
        }

        if (!$data[$randIndex] instanceof stdClass) {
            throw new TestException(
                message: 'Random data index is not an anonymous object.'
            );
        }

        return $data[$randIndex];
    }
}
