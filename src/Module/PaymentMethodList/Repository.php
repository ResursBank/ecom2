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
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\PaymentMethodElements;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\PaymentMethodType;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\PaymentMethodTypeMap;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\PaymentMethodTypeMapCollection;
use Resursbank\Ecom\Lib\Repository\Api\PaymentMethodElements\Post;
use Resursbank\Ecom\Lib\Repository\Cache;
use stdClass;
use Throwable;

/**
 * Interaction with Payment Method Elements API to collect method type details.
 */
class Repository
{
    use ExceptionLog;

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
    public static function getPaymentMethodTypes(
        PaymentMethodCollection $paymentMethods
    ): PaymentMethodTypeMapCollection {
        try {
            $cache = self::getCache(paymentMethods: $paymentMethods);
            $result = $cache->read();

            if (!$result instanceof PaymentMethodTypeMapCollection) {
                $result = self::getApi(paymentMethods: $paymentMethods)->call();

                if (!$result instanceof PaymentMethodTypeMapCollection) {
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

    public static function getCache(
        PaymentMethodCollection $paymentMethods
    ): Cache {
        $id = '';

        /** @var PaymentMethod $paymentMethod */
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
     * @throws ConfigException
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    public static function getApi(
        PaymentMethodCollection $paymentMethods
    ): Post {
        return new Post(
            model: PaymentMethodTypeMap::class,
            route: PaymentMethodElements::PAYMENT_METHODS_ROUTE . '/types',
            params: [
                'storeId' => Config::getStoreId(),
                'paymentMethodId' => array_map(
                    /** @phpstan-ignore-next-line */
                    callback: static fn (PaymentMethod $method) => $method->id,
                    array: iterator_to_array(iterator: $paymentMethods)
                )
            ],
            /** @phpstan-ignore-next-line */
            customModelConverter: static function (stdClass $data): Collection|Model {
                // This custimzed model converter is required because the
                // Payment Method Elements API will return data strucutred
                // inside an anonymous array, which is not compatible with the
                // generic converter we've used for other API implementations.

                self::validateApiResponse(data: $data);

                // Extract the types from the first element of the data array.
                $data = (array) $data->data[0]->types;

                $typeMap = [];

                foreach ($data as $paymentMethodId => $typeString) {
                    $typeMap[] = new PaymentMethodTypeMap(
                        paymentMethodId: $paymentMethodId,
                        type: PaymentMethodType::from(value: $typeString)
                    );
                }

                return new PaymentMethodTypeMapCollection(data: $typeMap);
            }
        );
    }

    /**
     * Validates response from the API. Abstracted from main function due to
     * high cognitive complexity.
     *
     * @throws ValidationException
     */
    private static function validateApiResponse(stdClass $data): void
    {
        if (
            !isset($data->data) ||
            !is_array(value: $data->data) ||
            !isset($data->data[0]) ||
            !$data->data[0] instanceof stdClass ||
            !isset($data->data[0]->types) ||
            !$data->data[0]->types instanceof stdClass
        ) {
            throw new ValidationException(
                message: 'Expected data to be an array of payment method type maps.'
            );
        }
    }
}
