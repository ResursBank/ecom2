<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Repository;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Post;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Resursbank\Ecom\Module\Rco\Traits\Repository as RepositoryTraits;
use Throwable;

/**
 * Handles cancel calls for RCO+.
 */
class Cancel
{
    use RepositoryTraits;

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
                event: $checkout->isCancelled() ?
                    Event::CANCELED :
                    Event::PARTIALLY_CANCELLED,
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
}
