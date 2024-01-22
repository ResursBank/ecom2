<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
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
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
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
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Throwable;

use function is_object;

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
        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $id,
            event: Event::CAPTURE_REQUESTED,
            user: User::ADMIN,
            extra: (
                $createTransaction?->transactionLines !== null
            ) ? Price::format(
                value: $createTransaction->transactionLines->getTotal() / 100
            ) : null
        ));

        try {
            $parameters = $createTransaction?->toArray() ?? [];

            $response = (new Post(
                route: Rco::CHECKOUT_ROUTE . '/' . $id . '/payment/capture',
                version: $version,
                params: $parameters
            ))->call(forceObject: empty($parameters));

            $checkout = self::validateCheckoutModel(model: $response);

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $id,
                event: $checkout->isCaptured() ? Event::CAPTURED : Event::PARTIALLY_CAPTURED,
                user: User::ADMIN,
                result: Result::SUCCESS
            ));

            return $checkout;
        } catch (Throwable $error) {
            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $id,
                event: Event::REQUEST_FAILED,
                user: User::ADMIN,
                extra: PaymentHistoryRepository::getError(error: $error),
                result: Result::ERROR
            ));

            throw $error;
        }
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
        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $id,
            event: Event::CANCEL_REQUESTED,
            user: User::ADMIN
        ));

        try {
            $response = (new Post(
                route: Rco::CHECKOUT_ROUTE . '/' . $id . '/payment/cancel',
                version: $version
            ))->call(forceObject: true);

            $checkout = self::validateCheckoutModel(model: $response);

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $id,
                event: $checkout->isCancelled() ? Event::CANCELED : Event::PARTIALLY_CANCELLED,
                user: User::ADMIN,
                result: Result::SUCCESS
            ));

            return $checkout;
        } catch (Throwable $error) {
            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $id,
                event: Event::REQUEST_FAILED,
                user: User::ADMIN,
                extra: PaymentHistoryRepository::getError(error: $error),
                result: Result::ERROR
            ));

            throw $error;
        }
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
        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $id,
            event: Event::REFUND_REQUESTED,
            user: User::ADMIN,
            extra: $transactionLines !== null ?
                Price::format(
                    value: $transactionLines->getTotal() / 100
                ) : null
        ));

        try {
            $response = (new Post(
                route: Rco::CHECKOUT_ROUTE . '/' . $id . '/payment/refund',
                version: $version,
                params: $transactionLines !== null ? ['transactionLines' => $transactionLines->toArray()] : []
            ))->call(forceObject: $transactionLines === null);

            $checkout = self::validateCheckoutModel(model: $response);

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $id,
                event: $checkout->isRefunded() ? Event::REFUNDED : Event::PARTIALLY_REFUNDED,
                user: User::ADMIN,
                result: Result::SUCCESS
            ));

            return $checkout;
        } catch (Throwable $error) {
            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $id,
                event: Event::REQUEST_FAILED,
                user: User::ADMIN,
                extra: PaymentHistoryRepository::getError(error: $error),
                result: Result::ERROR
            ));

            throw $error;
        }
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
        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $data = $post ?? file_get_contents(filename: 'php://input');

            if (!$data) {
                throw new WebhookException(message: 'Missing data.');
            }

            $data = json_decode(
                json: (string)$post,
                associative: false,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );

            if (!is_object(value: $data)) {
                throw new WebhookException(
                    message: 'Failed converting submitted data into an object.'
                );
            }

            $result = DataConverter::stdClassToType(
                object: $data,
                type: Checkout::class
            );

            if (!$result instanceof Checkout) {
                throw new IllegalValueException(
                    message: 'Received data could not be converted to CheckoutDto instance.'
                );
            }
        } catch (Throwable $error) {
            Config::getLogger()->debug(message: $error);

            throw new WebhookException(
                message: Translator::translate(
                    phraseId: 'invalid-webhook-data'
                )
            );
        }

        return $result;
    }
}
