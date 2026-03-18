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

class Repository
{
    use ExceptionLog;

    public const SESSION_CACHE_KEY_PREFIX = 'resursbank-ecom-rws-session-';

    /**
     * @param string|null $identifier Unique user identifier (e.g. quote ID)
     * @return Session
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
    public static function getSession(
        ?string $identifier = null
    ): Session {
        $cache = Config::getCache();
        $cacheKey = self::SESSION_CACHE_KEY_PREFIX . $identifier;

        $response = (new Post(
            model: Session::class,
            route: 'stores/' . Config::getStoreId() . '/sessions',
            params: []
        ))->call();

        if (!$response instanceof Session) {
            throw new ApiException(
                message: 'Failed to resolve session.'
            );
        }

        return $response;
    }

    /**
     * @param float $amount
     * @param string $locale
     * @param CustomerType $customerType
     * @return PaymentMethodCollection
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
        CustomerType $customerType,
    ): PaymentMethodCollection {
        $sessionId = self::getSession()->id;

        $response = (new Get(
            model: PaymentMethod::class,
            route: 'sessions/' . $sessionId . '/payment-method-groups?amount=' .
                $amount . '&locale=' . $locale . '&customerType=' .
                $customerType->value,
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
