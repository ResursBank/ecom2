<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

use function is_string;

/**
 * API credentials configuration object.
 */
class Mapi
{
    /**
     * Production endpoint.
     */
    public const HOST_PROD = 'checkout.resurs.com';

    /**
     * Test endpoint.
     */
    public const HOST_TEST = 'omnitest.resurs.com';

    /**
     * @param StringValidation $stringValidation
     */
    public function __construct(
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     * @throws ValidationException
     * @throws EmptyValueException
     */
    public function getUrl(
        string $route,
        array $params = []
    ): string {
        $this->stringValidation->notEmpty(value: $route);
        $this->stringValidation->matchRegex(
            value: $route,
            pattern: '/^[a-z\d]+$/i'
        );

        $paramList = implode(separator: '/', array: array_map(
            static function ($v, $k): string {
                if (!is_string(value: $v)) {
                    throw new IllegalTypeException(
                        message: "Param $k must be string."
                    );
                }

                return is_string(value: $k) ? "$k/$v" : $v;
            },
            $params,
            array_keys(array: $params)
        ));

        return (
            'https://' .
            (Config::$instance->isProduction ? self::HOST_PROD : self::HOST_TEST) .
            "/$route" .
            ($paramList !== '' ? "/$paramList" : '')
        );
    }
}
