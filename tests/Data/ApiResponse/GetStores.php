<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Data\ApiResponse;

use Exception;
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
 * Retrieve mock data for tests relating to Store module.
 */
class GetStores
{
    /**
     * @var string
     */
    public static string $data = <<<EOD
[
    {
        "id": "7711fc3c-3246-552c-102e-b45a373c5960",
        "nationalStoreId": 1234,
        "countryCode": "SE",
        "tradeName": "Mega Store",
        "popularName": "Business Palace",
        "representativeId": "7711fc3c-3246-552c-102e-b45a373c5960"
    },
    {
        "id": "3217ac8b-6942-452b-990f-c32f393c5977",
        "nationalStoreId": 99996,
        "countryCode": "NO",
        "tradeName": "Popular Candy",
        "popularName": "Dentists Office",
        "representativeId": "3217ac8b-6942-452b-990f-c32f393c5977"
    }
]
EOD;

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
