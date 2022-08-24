<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment;

use Error;
use Exception;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Module\Payment\Api\FindPayment;

class Repository
{
    /**
     * Write information to debug log.
     *
     * @param string $cause
     * @param Exception|Error $exception
     * @param string $data
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
     * @param string $orderReference
     * @param string $governmentId
     * @param FindPayment $api
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public static function findPayment(
        string $storeId,
        string $orderReference = '',
        string $governmentId = '',
        FindPayment $api = new FindPayment()
    ) {
        return $api->exec(
            $storeId,
            $orderReference,
            $governmentId
        );
    }
}
