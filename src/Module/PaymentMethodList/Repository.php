<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethodList;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodTypeMap;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodTypeMapCollection;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\PaymentMethod\Api\ApplicationDataSpecification;
use Resursbank\Ecom\Module\Widget\UniqueSellingPoint\Html;
use Throwable;

/**
 * Interaction with Payment Method entities and related functionality.
 */
class Repository
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
    public static function getPaymentMethodTypes(
        ?PaymentMethodCollection $paymentMethods = null,
    ): PaymentMethodCollection {
        try {
            $cache = self::getCache(paymentMethods: $paymentMethods);
            $result = $cache->read();

            if (!$result instanceof PaymentMethodTypeMapCollection) {
                $result = self::getApi(paymentMethods: $paymentMethods)->call();

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

    public static function getCache(
        ?PaymentMethodCollection $paymentMethods = null
    ): Cache {
        $id = '';

        foreach ($paymentMethods as $paymentMethod) {
            $id .= $paymentMethod->id;
        }

        return new Cache(
            key: 'payment-method-types-' . sha1(
                string: serialize(value: $id)
            ),
            model: PaymentMethodTypeMap::class,
            ttl: 3600
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws ConfigException
     */
    public static function getApi(
        ?PaymentMethodCollection $paymentMethods = null
    ): Get {
        return new Get(
            model: PaymentMethod::class,
            route: Rws::STORE_ROUTE . '/' . Config::getStoreId() .
            '/payment_methods',
            params: compact('amount'),
            extractProperty: 'content'
        );
    }
}
