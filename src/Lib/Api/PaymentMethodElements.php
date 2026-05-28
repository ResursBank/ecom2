<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * API for Resurs Payment Method Elements.
 */
class PaymentMethodElements
{
    public const URL_TEST = 'https://api.checkout.int.resurs.cloud/mock/payment/public/v1/';
    public const PAYMENT_METHODS_ROUTE = 'payment-methods';

    /**
     * @throws EmptyValueException
     */
    public function getUrl(string $route): string
    {
        if (!Strings::notEmpty(value: $route)) {
            throw new EmptyValueException(message: 'Route cannot be empty.');
        }

        return self::URL_TEST . $route;
    }
}
