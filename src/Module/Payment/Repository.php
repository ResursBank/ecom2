<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment;

use Error;
use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Module\Payment\Api\Capture;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Module\Payment\Api\Search;
use Resursbank\Ecom\Module\Payment\Api\GetPayment;
use Resursbank\Ecom\Module\Payment\Models\Payment;

/**
 * Payment repository.
 */
class Repository
{
    use ExceptionLog;

    /**
     * @param string $storeId
     * @param string $orderReference
     * @param string $governmentId
     * @param Search $api
     * @return Collection
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function Search(
        string $storeId,
        string $orderReference = '',
        string $governmentId = '',
        Search $api = new Search()
    ): Collection {
        return $api->call(
            $storeId,
            $orderReference,
            $governmentId
        );
    }

    /**
     * @param string $orderReference
     * @param GetPayment $api
     *
     * @return Payment
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function getPayment(
        string $orderReference,
        GetPayment $api = new GetPayment()
    ): Payment {
        try {
            return $api->call(
                $orderReference
            );
        } catch (Exception $e) {
            self::logException(exception: $e);

            throw $e;
        }
    }

    /**
     * Capture payment
     *
     * @param string $orderReference
     * @return Payment
     */
    public static function capture(
        string $orderReference
    ): Payment
    {
        return (new Capture())->call(orderReference: $orderReference);
    }
}
