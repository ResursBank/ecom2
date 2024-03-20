<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Repository;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Get;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Throwable;

/**
 * Interaction with API endpoint to extract payment methods.
 */
class PaymentMethods
{
    use ExceptionLog;

    /**
     * NOTE: Parameters must be validated since they are utilized for our cache
     * keys.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public static function getPaymentMethods(
        string $storeId
    ): PaymentMethodCollection {
        try {
            $cache = self::getCache(storeId: $storeId);
            $result = $cache->read();

            if (!$result instanceof PaymentMethodCollection) {
                $result = self::getApi(storeId: $storeId)->call();

                if (!$result instanceof PaymentMethodCollection) {
                    throw new ApiException(message: 'Invalid API response.');
                }

                $result = self::setCollectionSortOrder(collection: $result);
                $cache->write(data: $result);
            }
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }

        return $result;
    }

    /**
     * Updates sort order of fetched payment methods.
     */
    public static function setCollectionSortOrder(
        PaymentMethodCollection $collection
    ): PaymentMethodCollection {
        /** @var PaymentMethod $method */
        foreach ($collection as $method) {
            /* @phpstan-ignore-next-line */
            $method->sortOrder = ((int) $collection->key() + 1) * 100;
        }

        return $collection;
    }

    /**
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    public static function getCache(
        string $storeId
    ): Cache {
        self::validateStoreId(storeId: $storeId);

        return new Cache(
            key: 'payment-methods-' . sha1(
                string: serialize(value: compact(var_name: 'storeId'))
            ),
            model: PaymentMethod::class,
            ttl: 3600
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    public static function getApi(
        string $storeId
    ): Get {
        self::validateStoreId(storeId: $storeId);

        return new Get(
            route: Rco::PAYMENT_METHODS_ROUTE . '/' . $storeId,
            model: PaymentMethod::class,
            extractProperty: 'methods'
        );
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public static function getById(
        string $storeId,
        string $paymentMethodId
    ): ?PaymentMethod {
        $result = null;

        $paymentMethods = self::getPaymentMethods(storeId: $storeId);

        /** @var PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->methodId !== $paymentMethodId) {
                continue;
            }

            $result = $paymentMethod;
        }

        return $result;
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private static function validateStoreId(
        string $storeId
    ): void {
        $stringValidation = new StringValidation();
        $stringValidation->notEmpty(value: $storeId);
        $stringValidation->isUuid(value: $storeId);
    }
}
