<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Network\Header;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Put;
use Resursbank\Ecom\Module\Rco\Api\Init;

/**
 * Main entrypoint for interfacing with the RCO+ API programmatically.
 */
class Repository
{
    /**
     * Initialize a new checkout.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public static function init(
        Checkout $checkout
    ): Checkout {
        return (new Init())->call(checkout: $checkout);
    }

    /**
     * Replace cart contents with supplied Cart object.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function setCart(
        string $id,
        Checkout\Cart $cart,
        string $version
    ): Checkout {
        $headers = [];

        if ($version) {
            $headers[] = new Header(key: 'X-Checkout-Version', value: $version);
        }

        $response = (new Put(
            model: Checkout::class,
            route: 'api/checkout/' . $id . '/cart',
            params: [
                'items' => $cart->items->toArray()
            ],
            headers: $headers
        ))->call();

        if (!$response instanceof Checkout) {
            throw new IllegalTypeException(
                message: 'Expected ' . Checkout::class . ', got ' .
                $response::class
            );
        }

        return $response;
    }
}
