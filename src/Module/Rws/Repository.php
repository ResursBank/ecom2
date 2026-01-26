<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rws;

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
use Resursbank\Ecom\Lib\Api\Rws;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Network\Auth\Rws\SessionToken;
use Resursbank\Ecom\Lib\Repository\Api\Rws\Post;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Throwable;

class Repository
{
    use ExceptionLog;

    const SESSION_TOKEN_CACHE_KEY = 'rb-rws-session-token';

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
    public static function getSessionToken(): SessionToken
    {
        try {
            $token = Config::getSessionHandler()->get(key: self::SESSION_TOKEN_CACHE_KEY);

            if ($token !== '') {
                $token = DataConverter::stdClassToType(
                    object: json_decode(json: $token, associative: false),
                    type: SessionToken::class
                );

                if ($token instanceof SessionToken && !$token->isExpired()) {
                    return $token;
                }
            }

            $token = (new Post(
                model: SessionToken::class,
                route: Rws::ROUTE_SESSION,
                params: [
                    'storeId' => Config::getStoreId()
                ],
                // /** @phpstan-ignore-next-line */
                /*customModelConverter: static function (stdClass $data): Collection|Model {
                    // This custimzed model converter is required because the
                    // RWS API will return data strucutred inside an anonymous
                    // array, which is not compatible with the generic converter
                    // we've used for other API implementations.

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
                }*/
            ))->call();

            if (!$token instanceof SessionToken) {
                throw new ApiException('Failed to resolve session token.');
            }

            Config::getSessionHandler()->set(
                key: self::SESSION_TOKEN_CACHE_KEY,
                val: json_encode(value: $token, flags: JSON_THROW_ON_ERROR)
            );

            return $token;
        } catch (Throwable $e) {
            self::logException(exception: $e);
            throw $e;
        }
    }


    /**
     * Validates response from the API. Abstracted from main function due to
     * high cognitive complexity.
     *
     * @throws ValidationException
     */
   /* private static function validateApiResponse(stdClass $data): void
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
    }*/
}
