<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
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
     * @param ArrayValidation $arrayValidation
     * @param StringValidation $stringValidation
     */
    public function __construct(
        private readonly ArrayValidation $arrayValidation,
        private readonly StringValidation $stringValidation
    ) {
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function getUrl(
        string $route,
        array  $params = []
    ): string {
        $this->stringValidation->notEmpty(value: $route);

        if (count($params)) {
            $this->arrayValidation->isAssoc(data: $params);

            foreach ($params as $param) {
                if (!is_string(value: $param)) {
                    throw new ValidationException(
                        message: 'Param values must be strings.'
                    );
                }

                $this->stringValidation->notEmpty(value: $param);
            }
        }

        return (
            'https://' .
            Config::$instance->isProduction ? self::HOST_TEST : self::HOST_PROD .
            "/$route/" .
            implode(separator: '/', array: $params)
        );
    }
}
