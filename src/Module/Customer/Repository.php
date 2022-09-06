<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer;

use Error;
use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\GetAddressException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Data\Models\Address;
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
     * @param string $customerType
     * @param GetAddress $api
     * @return Address
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws GetAddressException
     * @todo Use CustomerType-enum instead of string.
     */
    public static function GetAddress(
        string $storeId,
        string $governmentId,
        string $customerType,
        GetAddress $api = new GetAddress()
    ): Address {
        try {
            return $api->call(
                $storeId,
                $governmentId,
                $customerType
            );
        } catch (Exception $e) {
            self::debug(
                cause: $e->getMessage(),exception: $e
            );
            throw $e;
        }
    }
}
