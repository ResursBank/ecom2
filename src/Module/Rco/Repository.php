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
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\ShippingMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Delete;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Get;
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
        $response = (new Post(
            route: Rco::CHECKOUT_ROUTE,
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
                'checkboxes' => $checkout->checkboxes?->toArray(),
                'merchant' => $checkout->merchant
            ]
        ))->call();

        return self::validateCheckoutModel(model: $response);
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
        $response = (new Put(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/cart',
            params: [
                'items' => $cart->items->toArray()
            ],
            version: $version
        ))->call();

        return self::validateCheckoutModel(model: $response);
    }

    /**
     * Update item quantity in cart.
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
    public static function patchCart(
        string $id,
        string $itemId,
        string $version,
        int $quantity
    ): Checkout {
        $response = (new Patch(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/cart',
            version: $version,
            params: [
                'items' => [
            [
                    'itemId' => $itemId,
                    'quantity' => $quantity
                    ]
                ]
            ]
        ))->call();

        return self::validateCheckoutModel(model: $response);
    }

    /**
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
        $response = (new Put(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/shipping/methods',
            version: $version,
            params: [
                'methods' => $shippingMethods->toArray()
            ]
        ))->call();

        return self::validateCheckoutModel(model: $response);
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
        $response = (new Delete(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/cart/items/' . $itemId,
            version: $version
        ))->call();

        return self::validateCheckoutModel(model: $response);
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
        $response = (new Put(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/order-reference',
            params: [
                'orderReference' => $orderReference
            ],
            version: $version
        ))->call();

        return self::validateCheckoutModel(model: $response);
    }

    /**
     * Fetch an existing Checkout.
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
    public static function get(
        string $id
    ): Checkout {
        $response = (new Get(route: Rco::CHECKOUT_ROUTE . '/' . $id))->call();

        return self::validateCheckoutModel(model: $response);
    }

    /**
     * Cancel a payment.
     *
     * @param string $id Checkout/payment ID
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
    public static function cancel(
        string $id,
        string $version
    ): Checkout {
        $response = (new Post(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/payment/cancel',
            version: $version
        ))->call();

        return self::validateCheckoutModel(model: $response);
    }

    /**
     * Centralised business logic to ensure type safety for all endpoint
     * implementations in this class.
     *
     * @throws IllegalTypeException
     */
    public static function validateCheckoutModel(
        Collection|Model $model
    ): Checkout {
        if (!$model instanceof Checkout) {
            throw new IllegalTypeException(
                message: 'Expected ' . Checkout::class . ', got ' . $model::class
            );
        }

        return $model;
    }
}
