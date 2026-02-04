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
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Rws;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Network\Auth\Rws\SessionToken;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Rws\Post;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;
use Throwable;

class Repository
{
    use ExceptionLog;

    public const SESSION_TOKEN_CACHE_KEY = 'rb-rws-session-token';

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
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
            $token = self::getSessionTokenFromCache();

            if ($token !== null) {
                return $token;
            }

            $token = (new Post(
                model: SessionToken::class,
                route: Rws::ROUTE_SESSION,
                params: [
                    'storeId' => Config::getStoreId()
                ],
                extractProperty: 'data'
            ))->call();

            if (!$token instanceof SessionToken) {
                throw new ApiException(
                    message: 'Failed to resolve session token.'
                );
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
     * Fetch payment methods.
     *
     * @return PaymentMethodCollection
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @throws AttributeCombinationException
     * @throws CurlException
     * @throws NotJsonEncodedException
     * @todo Set parameters correctly.
     */
    public static function getPaymentMethods(): PaymentMethodCollection
    {
        $token = self::getSessionToken();
        $parameters = [
            'storeId' => Config::getStoreId(),
            "sessionToken" => $token->token,
            "amount" => "1299",
            "customerType" => "B2C"
        ];
        // @todo Can't use collection as model...
        $result = (new Post(
            model: PaymentMethod::class,
            route: Rws::PAYMENT_METHODS_ROUTE,
            params: $parameters,
            extractProperty: 'data'
        ))->call();

        if (!$result instanceof PaymentMethodCollection) {
            throw new ApiException(
                message: 'Failed to resolve payment methods.'
            );
        }

        return $result;
    }

    /**
     * Get session token from cache.
     *
     * This method is private because it is only used internally by the repository.
     *
     * No individual tests makes sense, as the getSessionToken needs to test
     * everything this method does anyway.
     */
    private static function getSessionTokenFromCache(): ?SessionToken
    {
        $tokenData = Config::getSessionHandler()->get(
            key: self::SESSION_TOKEN_CACHE_KEY
        );

        if ($tokenData === null) {
            return null;
        }

        $data = json_decode(json: $tokenData, associative: false);

        if (!$data instanceof stdClass) {
            return null;
        }

        $token = DataConverter::stdClassToType(
            object: $data,
            type: SessionToken::class
        );

        if ($token instanceof SessionToken && !$token->isExpired()) {
            return $token;
        }

        return null;
    }
}
