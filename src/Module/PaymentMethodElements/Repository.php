<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethodElements;

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
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\CustomerType;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\Session;
use Resursbank\Ecom\Lib\Repository\Api\PaymentMethodElements\Get;
use Resursbank\Ecom\Lib\Repository\Api\PaymentMethodElements\Post;
use Resursbank\Ecom\Lib\Repository\Cache;

/**
 * Payment Method Elements repository class.
 */
class Repository
{
    use ExceptionLog;

    public const SESSION_CACHE_KEY_PREFIX = 'pme-session-';

    /**
     * @param string|null $identifier Unique user identifier (e.g. quote ID)
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws CacheException
     */
    public static function getSession(
        ?string $identifier = null,
        ?string $campaign = null
    ): Session {
        $cacheKey = self::SESSION_CACHE_KEY_PREFIX .
            sha1($identifier . ($campaign ?? ''));
        $cache = new Cache(key: $cacheKey, model: Session::class, ttl: 3600);

        $session = $cache->read();

        if (
            (
                $session instanceof Session &&
                $session->expired()
            ) ||
            !$session instanceof Session
        ) {
            $session = self::getSessionWithoutCache(campaign: $campaign);
            $cache->write(data: $session);
        }

        return $session;
    }

    /**
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function getSessionWithoutCache(?string $campaign = null): Session
    {
        $params = [];

        if ($campaign !== null) {
            $params['campaign'] = $campaign;
        }

        $session = (new Post(
            model: Session::class,
            route: 'stores/' . Config::getStoreId() . '/sessions',
            params: $params
        ))->call();

        if (!$session instanceof Session) {
            throw new ApiException(message: 'Failed to resolve session.');
        }

        return $session;
    }

    /**
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function getPaymentMethodGroups(
        float $amount,
        string $locale,
        string $sessionId,
        ?CustomerType $customerType = null
    ): PaymentMethodCollection {
        $route = 'sessions/' . $sessionId . '/payment-method-groups?amount=' .
            $amount . '&locale=' . $locale;

        if ($customerType !== null) {
            $route .= '&customerType=' . $customerType->value;
        }

        $response = (new Get(
            model: PaymentMethod::class,
            route: $route,
            params: [],
            extractProperty: 'data'
        ))->call();

        if (!$response instanceof PaymentMethodCollection) {
            throw new ApiException(
                message: 'Failed to resolve payment method groups.'
            );
        }

        return $response;
    }
}
