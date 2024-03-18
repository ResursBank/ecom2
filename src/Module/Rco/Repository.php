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
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Exception\WebhookException;
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateTransaction;
use Resursbank\Ecom\Lib\Model\Rco\CreateTransactionLineCollection;
use Resursbank\Ecom\Lib\Model\Rco\UpdateCheckout;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Delete;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Get;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Patch;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Post;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Put;
use Resursbank\Ecom\Module\Rco\Repository\Cancel;
use Resursbank\Ecom\Module\Rco\Repository\Capture;
use Resursbank\Ecom\Module\Rco\Repository\Refund;
use Resursbank\Ecom\Module\Rco\Repository\Webhook;
use Resursbank\Ecom\Module\Rco\Traits\Repository as RepositoryTraits;
use Throwable;

/**
 * Main entrypoint for interfacing with the RCO+ API programmatically.
 */
class Repository
{
    use RepositoryTraits;

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
        CreateCheckout $request
    ): Checkout {
        $response = (new Post(
            route: Rco::CHECKOUT_ROUTE,
            params: $request->toArray(full: true)
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
        CreateCart $cart,
        string $version
    ): Checkout {
        $response = (new Put(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/cart',
            version: $version,
            params: [
                'items' => $cart->items->toArray()
            ]
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
        CreateShippingMethodCollection $shippingMethods,
        string $version
    ): Checkout {
        $response = (new Put(
            route: Rco::CHECKOUT_ROUTE . '/' . $id . '/shipping/methods',
            version: $version,
            params: [
                'methods' => $shippingMethods->toArray(full: true)
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
            version: $version,
            params: [
                'orderReference' => $orderReference
            ]
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
        $response = (new Get(
            route: Rco::CHECKOUT_ROUTE . '/' . $id,
            model: Checkout::class
        ))->call();

        return self::validateCheckoutModel(model: $response);
    }

    /**
     * Update checkout state.
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
    public static function update(
        string $id,
        UpdateCheckout $data,
        string $version
    ): Checkout {
        $response = (new Put(
            route: Rco::CHECKOUT_ROUTE . '/' . $id,
            version: $version,
            params: $data->toArray()
        ))->call();

        return self::validateCheckoutModel(model: $response);
    }

    /**
     * Capture a payment.
     *
     * @param string $id Checkout/payment ID
     * @throws ValidationException
     * @throws AuthException
     * @throws EmptyValueException
     * @throws CurlException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ConfigException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws Throwable
     */
    public static function capture(
        string $id,
        string $version,
        ?CreateTransaction $createTransaction = null
    ): Checkout {
        return Capture::capture(
            id: $id,
            version: $version,
            createTransaction: $createTransaction
        );
    }

    /**
     * Cancel a payment.
     *
     * @param string $id Checkout/payment ID
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public static function cancel(
        string $id,
        string $version
    ): Checkout {
        return Cancel::cancel(id: $id, version: $version);
    }

    /**
     * Refund a payment.
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
     * @throws AttributeCombinationException
     * @throws Throwable
     */
    public static function refund(
        string $id,
        string $version,
        ?CreateTransactionLineCollection $transactionLines = null
    ): Checkout {
        return Refund::refund(
            id: $id,
            version: $version,
            transactionLines: $transactionLines
        );
    }

    /**
     * Convert php://input stream data to a CheckoutDto instance.
     *
     * @throws ConfigException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws WebhookException
     * @throws FilesystemException
     * @throws TranslationException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getWebhookRequestData(?string $post = null): Checkout
    {
        return Webhook::getRequestData(post: $post);
    }
}
