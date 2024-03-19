<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * API credentials configuration object.
 */
class Rco
{
    /**
     * Production endpoint.
     */
    public const URL_PROD = 'https://rco.resurs.com/';

    /**
     * Integration mock test endpoint.
     */
    public const URL_TEST = 'https://rco.integration.resurs.com/';

    /**
     * JWT Production endpoint.
     */
    public const AUTH_URL_PROD = 'https://apigw.resurs.com/';

    /**
     * JWT Test endpoint.
     */
    public const AUTH_URL_TEST = 'https://apigw.integration.resurs.com/';

    /**
     * Prefix route name for checkout.
     */
    public const CHECKOUT_ROUTE = 'api/checkout';

    /**
     * Prefix route name for payment methods.
     */
    public const PAYMENT_METHODS_ROUTE = 'api/payment-methods';

    public function __construct(
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }

    /**
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws ConfigException
     */
    public function getUrl(
        string $route
    ): string {
        $this->stringValidation->notEmpty(value: $route);

        return
            (Config::isProduction() ? self::URL_PROD : self::URL_TEST) .
            $route;
    }

    /**
     * Get URL for tokens individually since the urls are different to the API endpoints.
     *
     * @throws EmptyValueException
     * @throws ConfigException
     */
    public function getTokenUrl(string $route): string
    {
        $this->stringValidation->notEmpty(value: $route);

        return
            (Config::isProduction() ? self::AUTH_URL_PROD : self::AUTH_URL_TEST) .
            $route;
    }
}
