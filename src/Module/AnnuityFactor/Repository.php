<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor;

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
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformation;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformationCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Throwable;

/**
 * Interaction with Annuity factor entities and related functionality.
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
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @throws Throwable
     */
    public static function getAnnuityFactors(
        string $storeId,
        string $paymentMethodId
    ): AnnuityInformationCollection {
        try {
            $cache = self::getCache(
                storeId: $storeId,
                paymentMethodId: $paymentMethodId
            );

            $result = $cache->read();

            if (!$result instanceof AnnuityInformationCollection) {
                $result = self::getApi(
                    storeId: $storeId,
                    paymentMethodId: $paymentMethodId
                )->call();

                if (!$result instanceof AnnuityInformationCollection) {
                    throw new ApiException(message: 'Invalid API response.');
                }

                $cache->write(data: $result);
            }
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }

        return $result;
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
     * @throws Throwable
     * @throws ValidationException
     * @throws Throwable
     */
    public static function filterMethods(
        string $storeId,
        PaymentMethodCollection $paymentMethods
    ): PaymentMethodCollection {
        /** @var array<PaymentMethod> $arr */
        $arr = $paymentMethods->toArray();

        /** @var array<PaymentMethod> $result */
        $result = [];

        foreach ($arr as $method) {
            $factors = self::getAnnuityFactors(
                storeId: $storeId,
                paymentMethodId: $method->id
            );

            if ($factors->count() === 0) {
                continue;
            }

            $result[] = $method;
        }

        $result = new PaymentMethodCollection(data: $result);

        return $result->filterByPropertyValue(
            property: 'priceSignagePossible',
            value: true
        );
    }

    /**
     * @throws IllegalValueException
     */
    public static function getCache(
        string $storeId,
        string $paymentMethodId
    ): Cache {
        self::validateStoreId(storeId: $storeId);

        return new Cache(
            key: 'payment-method-annuity' . sha1(
                string: serialize(value: compact('storeId', 'paymentMethodId'))
            ),
            model: AnnuityInformation::class,
            ttl: 3600
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public static function getApi(
        string $storeId,
        string $paymentMethodId
    ): Get {
        self::validateStoreId(storeId: $storeId);

        return new Get(
            model: AnnuityInformation::class,
            route: Mapi::STORE_ROUTE . "/$storeId/payment_methods/$paymentMethodId/annuity_factors",
            params: [],
            extractProperty: 'content'
        );
    }

    /**
     * @throws IllegalValueException
     */
    private static function validateStoreId(
        string $storeId
    ): void {
        $stringValidation = new StringValidation();
        $stringValidation->isUuid(value: $storeId);
    }
}
