<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Data;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Models\StoreCollection;
use stdClass;

use function is_array;
use function is_int;

/**
 * Mock data for tests relating to Store module.
 */
class GetStores
{
    /**
     * @var string
     */
    public static string $data = <<<EOD
[
    {
        "id": "eba4e4ae-90e0-41d1-93e2-c45db5889bd6",
        "nationalStoreId": 8901,
        "countryCode": "SE",
        "name": "Camilmoblerse"
    },
    {
        "id": "ae6cb0b5-e0e8-4bce-8c40-078b90cdaca5",
        "nationalStoreId": 8967,
        "countryCode": "NO",
        "name": "CarSwipe MAPI"
    },
    {
        "id": "fbbee60d-f7d8-426e-bd3d-3b88af1c69ce",
        "nationalStoreId": 8912,
        "countryCode": "DK",
        "name": "Test butik"
    },
    {
        "id": "b1d42969-4be4-4b84-ba26-4755588bcbf1",
        "nationalStoreId": 8986,
        "countryCode": "FI",
        "name": "Glamazon SE"
    },
    {
        "id": "c7007fc2-9370-47fc-aabd-f4e18da6c1b0",
        "nationalStoreId": 8910,
        "countryCode": "SE",
        "name": "Eriks bil"
    },
    {
        "id": "ccd38a18-b124-4981-8302-8725420bc6ce",
        "nationalStoreId": 8982,
        "countryCode": "SE",
        "name": "Bunkerns Trafikskola"
    },
    {
        "id": "7be8bf04-aa2e-4deb-938f-beb1898ea81e",
        "nationalStoreId": 8983,
        "countryCode": "FI",
        "name": "Trolles Trafikskola"
    },
    {
        "id": "57f91f35-926a-499d-8b26-189ce878061c",
        "nationalStoreId": 8906,
        "countryCode": "DK",
        "name": "Pluto"
    },
    {
        "id": "801761ed-0e78-41a7-95d2-e5b514e0e28c",
        "nationalStoreId": 8966,
        "countryCode": "DK",
        "name": "Rolfs Flyg och Buss"
    },
    {
        "id": "05279178-8ae6-4536-986e-828cd5dad98f",
        "nationalStoreId": 8985,
        "countryCode": "FI",
        "name": "Sk\u00e5rebo"
    },
    {
        "id": "0a4451d2-bc12-4be6-86bf-574a81c0d245",
        "nationalStoreId": 8950,
        "countryCode": "NO",
        "name": "Solberga Teknik"
    },
    {
        "id": "db51fe4f-a74d-4025-9d1d-a49b7aa0fde5",
        "nationalStoreId": 8902,
        "countryCode": "NO",
        "name": "Testkonto onboarding"
    },
    {
        "id": "26f82a8f-a5b5-4faa-91ed-062b61a2a518",
        "nationalStoreId": 8984,
        "countryCode": "SE",
        "name": "Watski SE"
    },
    {
        "id": "d3926cfb-760c-4df8-bd44-28319f60feab",
        "nationalStoreId": 8904,
        "countryCode": "DK",
        "name": "Yrkesbutiken"
    }
]
EOD;

    /**
     * Get a single, random, store from list of stores.
     *
     * @return stdClass
     * @throws JsonException
     * @throws TestException
     */
    public static function getRandomStoreData(): stdClass
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
        $item = array_rand(array: $data);

        if (!is_int(value: $item)) {
            throw new TestException(
                message: 'Failed to resolve random data index.'
            );
        }

        if (!$data[$item] instanceof stdClass) {
            throw new TestException(
                message: 'Random data index is not an anonymous object.'
            );
        }

        return $data[$item];
    }

    /**
     * Get all stores.
     *
     * @return StoreCollection
     * @throws JsonException
     * @throws TestException
     * @throws ReflectionException
     * @psalm-suppress MixedInferredReturnType
     */
    public static function getStores(): StoreCollection
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $data = json_decode(
            json: self::$data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        if (!is_array(value: $data)) {
            throw new TestException(message: 'Test data corrupt.');
        }

        /** @psalm-suppress MixedReturnStatement */
        return DataConverter::arrayToCollection(
            data: $data,
            targetType: Store::class
        );
    }
}
