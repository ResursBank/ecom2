<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Module\Common\Address;
use Resursbank\Ecom\Module\Customer\Api\GetAddress;
use Resursbank\Ecom\Module\Customer\Enum\CustomerType;

class Repository
{
    /**
     * Write information to debug log.
     *
     * @param string $cause
     * @param Exception|Error $exception
     * @param string $data
     *
     * @return void
     */
    private static function debug(
        string $cause,
        Exception|Error $exception,
        string $data = ''
    ): void {
        Config::$instance->logger->debug(message: '--------------------------');
        Config::$instance->logger->debug(message: $cause);
        Config::$instance->logger->debug(message: $exception);
        Config::$instance->logger->debug(message: serialize(value: $data));
        Config::$instance->logger->debug(message: '--------------------------');
    }

    /**
     * @param string $storeId
     * @param string $governmentId
     * @param CustomerType $customerType
     * @param GetAddress $api
     * @return Collection
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @todo Use CustomerType-enum instead of string.
     */
    public static function GetAddress(
        string $storeId,
        string $governmentId,
        string $customerType,
        GetAddress $api = new GetAddress()
    ): Address {
        return $api->call(
            $storeId,
            $governmentId,
            $customerType
        );
    }
}
