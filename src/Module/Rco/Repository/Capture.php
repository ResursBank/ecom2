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
use Resursbank\Ecom\Lib\Model\Rco\CreateTransaction;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Post;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Resursbank\Ecom\Module\Rco\Repository;
use Resursbank\Ecom\Module\Rco\Traits\Repository as RepositoryTraits;
use Throwable;

/**
 * Handles capture calls for RCO+.
 */
class Capture
{
    use RepositoryTraits;

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
        $checkout = Repository::get(id: $id);

        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $id,
            event: Event::CAPTURE_REQUESTED,
            user: User::ADMIN,
            extra: (
                $createTransaction?->transactionLines !== null
            ) ? Price::format(
                value: $createTransaction->transactionLines->getTotal() / 100,
                currencyFormat: $checkout->getCurrencyFormat(),
                currencySymbol: $checkout->getCurrencySymbol()
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
                event: $checkout->isCaptured() ?
                    Event::CAPTURED :
                    Event::PARTIALLY_CAPTURED,
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
