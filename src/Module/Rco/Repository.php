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
use Resursbank\Ecom\Lib\Model\Rco\Shipping\ShippingMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Delete;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Patch;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Post;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Put;

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

    /**
     * Update item quantity in cart.
     *
     * @param string $id Checkout ID
     * @param string $itemId Cart item ID
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public static function patchCart(
        string $id,
        string $itemId,
        string $version,
        int $quantity
    ): Checkout {
        $headers = [];

        if ($version) {
            $headers[] = new Header(key: 'X-Checkout-Version', value: $version);
        }

        $response = (new Patch(
            model: Checkout::class,
            route: 'api/checkout/' . $id . '/cart/item/' . $itemId,
            params: [
                'quantity' => $quantity
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

    /**
<<<<<<< HEAD
     * Set shipping methods on Checkout.
     *
     * @param string $id Checkout ID
     * @param string $version Checkout version
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
    public static function setShippingMethods(
        string $id,
        ShippingMethodCollection $shippingMethods,
        string $version
    ): Checkout {
        $headers = [
            new Header(key: 'X-Checkout-Version', value: $version)
        ];

        $response = (new Put(
            model: Checkout::class,
            route: 'api/checkout/' . $id . '/shipping/methods',
            params: [
                'methods' => $shippingMethods->toArray()
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

    /**
     * Deletes specified cart item.
     *
     * @param string $id Checkout ID
     * @param string $itemId Cart item ID
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
    public static function deleteCartItem(
        string $id,
        string $itemId,
        string $version
    ): Checkout {
        $headers = [
            new Header(key: 'X-Checkout-Version', value: $version)
        ];

        $response = (new Delete(
            model: Checkout::class,
            route: 'api/checkout/' . $id . '/cart/item/' . $itemId,
            params: [],
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

    /**
     * Set order reference on Checkout.
     *
     * @param string $id Checkout ID
     * @param string $orderReference Order reference
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
    public static function setOrderReference(
        string $id,
        string $orderReference,
        string $version
    ): Checkout {
        $headers = [
            new Header(key: 'X-Checkout-Version', value: $version)
        ];

        $response = (new Put(
            model: Checkout::class,
            route: 'api/checkout/' . $id . '/order-reference',
            params: [
                'orderReference' => $orderReference
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
