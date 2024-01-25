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
use Resursbank\Ecom\Lib\Model\Rco\CreateTransactionLineCollection;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Post;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Resursbank\Ecom\Module\Rco\Traits\Repository as RepositoryTraits;
use Throwable;

/**
 * Handles refund calls for RCO+.
 */
class Refund
{
    use RepositoryTraits;

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
                params: $transactionLines !== null ? [
                    'transactionLines' => $transactionLines->toArray()
                ] : []
            ))->call(forceObject: $transactionLines === null);

            $checkout = self::validateCheckoutModel(model: $response);

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $id,
                event: $checkout->isRefunded() ?
                    Event::REFUNDED :
                    Event::PARTIALLY_REFUNDED,
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
