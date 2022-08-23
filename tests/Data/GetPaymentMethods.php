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
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethodCollection;
use stdClass;

use function is_array;
use function is_int;

/**
 * Mock data for tests relating to PaymentMethod module.
 *
 * @todo Add more data to this class.
 */
class GetPaymentMethods
{
    /**
     * @var string
     */
    public static string $data = <<<EOD
[
    {
        "id": "8b9dab18-e1fe-430e-891b-42edb89ba77a",
        "customerType": "NATURAL",
        "displayOrder": 8,
        "description": "Swisha",
        "validFrom": "2019-03-11",
        "validTo": "2119-03-11",
        "supportedActions": [
            "AUTHORIZE",
            "DEBIT",
            "CREDIT"
        ],
        "minPurchaseLimit": 1,
        "maxPurchaseLimit": 1000000,
        "minApplicationLimit": 1,
        "maxApplicationLimit": 1000000,
        "type": "SWISH",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "7a47f271-2f27-41ce-a55c-e65e3daed74f",
        "customerType": "NATURAL",
        "displayOrder": 0,
        "description": "Ett betalsätt",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-31",
        "supportedActions": [
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "LIMIT_RAISE",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 1,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 10.00,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_REVOLVING_CREDIT",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "6811fa7c-3145-472b-982d-e20a353a5464",
        "customerType": "NATURAL",
        "displayOrder": 1,
        "description": "Faktura privat (noCheck <10.000)",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-31",
        "supportedActions": [
            "APPLY_FOR_CREDIT",
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 10.00,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 10.00,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_INVOICE",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "917983f9-3ec8-4dec-8bbd-a75e74f4a3de",
        "customerType": "NATURAL",
        "displayOrder": 2,
        "description": "Fakturakampanj",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-31",
        "supportedActions": [
            "APPLY_FOR_CREDIT",
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 10.00,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 10.00,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_INVOICE",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "6517644f-c2b6-45b4-a60d-037601f063ef",
        "customerType": "NATURAL",
        "displayOrder": 3,
        "description": "Faktura numerisk betalmetodid",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-31",
        "supportedActions": [
            "APPLY_FOR_CREDIT",
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 10.00,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 10.00,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_INVOICE",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "e0dd2017-f388-497e-9ef9-ff9366853b98",
        "customerType": "NATURAL",
        "displayOrder": 4,
        "description": "Delbetalning",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-31",
        "supportedActions": [
            "APPLY_FOR_CREDIT",
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 10.00,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 10.00,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_PART_PAYMENT",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "80e67fc2-1dda-4783-ac26-be9b8f4b297f",
        "customerType": "NATURAL",
        "displayOrder": 5,
        "description": "Betala med Resurs-kort",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-30",
        "supportedActions": [
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "LIMIT_RAISE",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 1,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 10.00,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_CARD",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "645af047-ad87-47b5-9b46-721316fdfe85",
        "customerType": "NATURAL",
        "displayOrder": 6,
        "description": "Ansök och betala med nytt Resurs-kort",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-31",
        "supportedActions": [
            "APPLY_FOR_NEW_ACCOUNT",
            "APPLY_FOR_CREDIT",
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 1,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 1000,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_NEW_CARD",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "866416fe-031a-43f7-b3a6-fe7b04509b77",
        "customerType": "LEGAL",
        "displayOrder": 7,
        "description": "FtgFkt",
        "validFrom": "2022-08-15",
        "validTo": "2100-12-31",
        "supportedActions": [
            "APPLY_FOR_CREDIT",
            "AUTHORIZE",
            "CREDIT",
            "DEBIT",
            "PART_CREDIT",
            "PART_DEBIT"
        ],
        "minPurchaseLimit": 10.00,
        "maxPurchaseLimit": 50000.00,
        "minApplicationLimit": 10.00,
        "maxApplicationLimit": 50000.00,
        "type": "RESURS_INVOICE",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "4fcf7608-59df-4c4b-b49d-11063c58be7a",
        "displayOrder": 9,
        "description": "Trusly direktbetalning",
        "validFrom": "2021-06-30",
        "validTo": "2121-06-30",
        "supportedActions": [
            "AUTHORIZE",
            "DEBIT",
            "CREDIT"
        ],
        "minPurchaseLimit": 0.01,
        "maxPurchaseLimit": 1000000,
        "minApplicationLimit": 0.01,
        "maxApplicationLimit": 1000000,
        "type": "INTERNET",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "d4753f08-3f62-4b77-8d0d-aea3d315815f",
        "customerType": "NATURAL",
        "displayOrder": 10,
        "description": "Bankkort Visa/Mastercard",
        "validFrom": "2019-02-22",
        "validTo": "2119-02-22",
        "supportedActions": [
            "AUTHORIZE",
            "DEBIT",
            "CREDIT"
        ],
        "minPurchaseLimit": 0.01,
        "maxPurchaseLimit": 1000000,
        "minApplicationLimit": 0.01,
        "maxApplicationLimit": 1000000,
        "type": "DEBIT_CARD",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "2e24a31d-c0b3-4059-a0bf-6b08e6784e59",
        "customerType": "NATURAL",
        "displayOrder": 10,
        "description": "Kreditkort Visa/Mastercard",
        "validFrom": "2019-02-22",
        "validTo": "2119-02-22",
        "supportedActions": [
            "AUTHORIZE",
            "DEBIT",
            "CREDIT"
        ],
        "minPurchaseLimit": 0.01,
        "maxPurchaseLimit": 1000000,
        "minApplicationLimit": 0.01,
        "maxApplicationLimit": 1000000,
        "type": "CREDIT_CARD",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    },
    {
        "id": "97558cd1-fa0d-453e-bb88-59f317aba64d",
        "displayOrder": 11,
        "description": "Bankkortsbetalning",
        "validFrom": "2019-04-25",
        "validTo": "2119-04-25",
        "supportedActions": [
            "AUTHORIZE",
            "DEBIT",
            "CREDIT"
        ],
        "minPurchaseLimit": 0.01,
        "maxPurchaseLimit": 1000000,
        "minApplicationLimit": 0.01,
        "maxApplicationLimit": 1000000,
        "type": "DEBIT_CARD",
        "status": {
            "disabled": false,
            "requireLimitRaise": false,
            "disabledReasons": []
        }
    }
]
EOD;

    /**
     * Get a single, random, paymentMethod from list of paymentMethods.
     *
     * @return stdClass
     * @throws JsonException
     * @throws TestException
     */
    public static function getRandomPaymentMethodData(): stdClass
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
     * Get all paymentMethods.
     *
     * @return PaymentMethodCollection
     * @throws JsonException
     * @throws TestException
     * @throws ReflectionException
     * @psalm-suppress MixedInferredReturnType
     */
    public static function getPaymentMethods(): PaymentMethodCollection
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
            targetType: PaymentMethod::class
        );
    }
}
