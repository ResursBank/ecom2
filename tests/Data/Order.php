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

/**
 * Mock data for tests relating to Store module.
 *
 * @todo Add more data to this class.
 */
class Order
{
    /**
     * @var string
     */
    public static string $data = <<<EOD
{
    "orderLines": [
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
    ],
    "orderReference": "aklsfjah234oiaslhjfd"
}
EOD;

    /**
     * @return stdClass
     * @throws JsonException
     * @throws TestException
     */
    public static function getData(): stdClass
    {
        $data = json_decode(
            json: self::$data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        if (!$data instanceof stdClass) {
            throw new TestException(
                message: '$data is not a valid stdClass.'
            );
        }

        return $data;
    }
}
