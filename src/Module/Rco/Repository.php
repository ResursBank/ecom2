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
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Post;

/**
 * Main entrypoint for interfacing with the RCO+ API programmatically.
 */
class Repository
{
    /**
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
        $result = (new Post(
            model: Checkout::class,
            route: 'api/checkout',
            params: [
                'orderReference' => $checkout->orderReference,
                'options' => $checkout->options,
                'locale' => $checkout->locale,
                'currency' => $checkout->currency,
                'cart' => $checkout->cart->toArray(),
                'customer' => $checkout->customer,
                'redirects' => $checkout->redirects,
                'callbacks' => $checkout->callbacks,
                'webhooks' => $checkout->webhooks,
                'checkboxes' => $checkout->checkboxes->toArray(),
                'merchant' => $checkout->merchant
            ]
        ))->call();

        if (!$result instanceof Checkout) {
            throw new IllegalTypeException(
                message: 'Expected ' . Checkout::class . ', got ' . $result::class
            );
        }

        return $result;
    }
}
