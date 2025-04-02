<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\CallbackInterface;
use Resursbank\Ecom\Lib\Model\Callback\Management;
use Resursbank\Ecom\Lib\Model\Callback\TestResponse;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Post;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Repository as PaymentRepository;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Throwable;

/**
 * Callback repository.
 */
class Repository
{
    use ExceptionLog;

    /**
     * Trigger test callback.
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
     * @noinspection PhpUnused
     */
    public static function triggerTest(
        string $url,
        StringValidation $stringValidation = new StringValidation()
    ): TestResponse {
        Config::getLogger()->debug(message: 'Triggering test callback.');

        $stringValidation->isUrl(value: $url);

        $request = new Post(
            model: TestResponse::class,
            route: Mapi::CALLBACK_ROUTE . '/test',
            params: ['url' => $url]
        );

        $response = $request->call();

        if (!$response instanceof TestResponse) {
            throw new IllegalValueException(
                message: 'Unexpected model instance returned from test callback.'
            );
        }

        return $response;
    }

    /**
     * @throws ConfigException
     */
    public static function process(
        CallbackInterface $callback,
        callable $process
    ): int {
        $paymentId = $callback->getCheckoutId() ?? $callback->getPaymentId();

        self::trackInit(paymentId: $paymentId, callback: $callback);
        self::addDebugLogs(callback: $callback);

        $code = 202;

        try {
            $process($callback);

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $paymentId,
                event: Event::CALLBACK_COMPLETED,
                user: User::RESURSBANK,
                result: Result::SUCCESS
            ));
        } catch (Throwable $e) {
            self::logException(exception: $e);
            $code = 408;

            if ($e instanceof HttpException) {
                $code = $e->getCode();
            }

            self::trackError(paymentId: $paymentId, error: $e);
        }

        Config::getLogger()->debug(message: "Responding with code $code");

        return $code;
    }

    /**
     * Log error in payment history.
     *
     * @throws ConfigException
     */
    public static function trackError(
        string $paymentId,
        Throwable $error
    ): void {
        try {
            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $paymentId,
                event: Event::CALLBACK_FAILED,
                user: User::ADMIN,
                extra: PaymentHistoryRepository::getError(error: $error),
                result: Result::ERROR
            ));
        } catch (Throwable $e) {
            self::logException(exception: $e);
        }
    }

    /**
     * Log callback initialization in payment history.
     *
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public static function trackInit(
        string $paymentId,
        CallbackInterface $callback
    ): void {
        try {
            $extra = null;

            if ($callback instanceof Authorization) {
                $event = Event::CALLBACK_AUTHORIZATION;
                $extra = $callback->status->value;
            } else {
                $event = Event::CALLBACK_MANAGEMENT;
            }

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $paymentId,
                event: $event,
                user: User::RESURSBANK,
                extra: $extra
            ));
        } catch (Throwable $e) {
            self::logException(exception: $e);
        }
    }

    /**
     * Append debug log entries.
     *
     * @throws ConfigException
     */
    public static function addDebugLogs(
        CallbackInterface $callback
    ): void {
        if ($callback instanceof Management) {
            Config::getLogger()->debug(
                message: sprintf(
                    'Processing management callback for %s, action %s (%s)',
                    $callback->getPaymentId(),
                    $callback->action->value,
                    $callback->actionId
                )
            );
        }

        if (!($callback instanceof Authorization)) {
            return;
        }

        Config::getLogger()->debug(
            message: sprintf(
                'Processing authorization callback for %s, status %s',
                $callback->getPaymentId(),
                $callback->status->value
            )
        );
    }

    /**
     * Callbacks are ready for processing if one of the following conditions are
     * met:
     *
     * 1. The order success page has been reached.
     * 1. The order failure page has been reached.
     * 2. The payment is older than 60 seconds.
     *
     * Our Authorization callback, for example, will manipulate order status.
     * When a customer leaves the gateway, returning to the order success page
     * at the merchant website, the Authorization callback will fire at the
     * same time.
     *
     * Both the customer landing on the success page, and the Authorization
     * callback being received will manipulate the order status, syncing it
     * against the payment at Resurs Bank, or applying an initial status.
     *
     * This can therefore cause a race condition, consider the following
     * scenario:
     *
     * 1. Customer leaves gateway.
     * 2. Authorization callback is executed.
     * 3. Authorization callback is processed, order status is synced to FROZEN.
     * 4. Customer lands on success page.
     * 5. Order status updates to the initial state of PENDING.
     *
     * In this scenario the order status will move from FROZEN -> PENDING,
     * rather than the expected PENDING -> FROZEN.
     *
     * Since initial status, if ever, will be applied on the success page, we
     * want to ensure that the success page is reached before processing
     * callbacks.
     *
     * The customer may of course fail to reach the success page, for example if
     * the customer closes the browser window before the page has loaded.
     *
     * This is why we also consider the payment age, if the payment is older
     * than 60 seconds, we can assume that the customer has left the gateway
     * and that we can sync the order status safely.
     *
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
     * @throws NotJsonEncodedException
     */
    public static function isReady(
        string $paymentId
    ): bool {
        $successPageReached = PaymentHistoryRepository::hasExecuted(
            paymentId: $paymentId,
            event: Event::REACHED_ORDER_SUCCESS_PAGE
        );

        if ($successPageReached) {
            Config::getLogger()->error(message: 'Order success page reached.');
        }

        $failurePageReached = PaymentHistoryRepository::hasExecuted(
            paymentId: $paymentId,
            event: Event::REACHED_ORDER_FAILURE_PAGE
        );

        if ($failurePageReached) {
            Config::getLogger()->error(message: 'Order failure page reached.');
        }

        $paymentOlderThan60Seconds = PaymentRepository::get(paymentId: $paymentId)
            ->isOlderThan(seconds: 60);

        return $failurePageReached || $successPageReached || $paymentOlderThan60Seconds;
    }
}
