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
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\UserSettingsException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\CallbackInterface;
use Resursbank\Ecom\Lib\Model\Callback\CreditApplication;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Result as CallbackResult;
use Resursbank\Ecom\Lib\Model\Callback\Management;
use Resursbank\Ecom\Lib\Model\Callback\TestResponse;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Post;
use Resursbank\Ecom\Lib\UserSettings\Url;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentHistory\Repository as EcomPaymentHistoryRepository;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Resursbank\Ecom\Module\UserSettings\Repository as UserSettingsRepository;
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
     * @throws UserSettingsException
     * @throws NotJsonEncodedException
     */
    public static function triggerTest(
        ?string $url = null
    ): TestResponse {
        Config::getLogger()->debug(message: 'Triggering test callback.');

        $url ??= UserSettingsRepository::getUrl(url: Url::CALLBACK_TEST_URL);

        if (!Strings::isUrl(value: $url)) {
            throw new IllegalValueException(
                message: 'URL must be a valid url.'
            );
        }

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
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws HttpException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     */
    public static function process(
        CallbackInterface $callback,
        ?callable $process = null
    ): int {
        $paymentId = $callback->getCheckoutId() ?? $callback->getPaymentId();

        // If callback is not ready to be processed, throw error.
        self::handleNotReady(callback: $callback);

        self::trackInit(paymentId: $paymentId, callback: $callback);
        self::addDebugLogs(callback: $callback);

        $code = 202;

        try {
            if ($process !== null) {
                $result = $process($callback);

                // We don't want to try to write to the payment history if the
                // order has been deleted.
                if ($result !== CallbackResult::DELETED) {
                    PaymentHistoryRepository::write(entry: new Entry(
                        paymentId: $paymentId,
                        event: Event::CALLBACK_COMPLETED,
                        user: User::RESURSBANK,
                        result: Result::SUCCESS
                    ));
                }
            }
        } catch (Throwable $error) {
            $code = self::handleProcessingError(
                error: $error,
                paymentId: $paymentId
            );
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
                result: Result::ERROR,
                extra: PaymentHistoryRepository::getError(error: $error)
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
                $extra = $callback->getStatus()?->value;
            } elseif ($callback instanceof CreditApplication) {
                $event = Event::CALLBACK_CREDIT_APPLICATION;
                $extra = $callback->getStatus()?->value;
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
                $callback->getStatus()?->value
            )
        );
    }

    /**
     * Check if payment is ready for processing.
     *
     * Callbacks are ready for processing if one of the following conditions are
     * met:
     *
     * 1. The order success page has been reached.
     * 2. The order failure page has been reached.
     *
     * There are two other conditions based on the type of callback:
     *
     * a. If the callback is a Credit Application callback, it is always ready.
     * b. If we have already received the first Authorization callback, and a
     *   subsequent Authorization callback is being processed, it is ready.
     *   See a detailed explanation below.
     *
     * Our Authorization callback will manipulate order status. When a customer
     * leaves the gateway, returning to the order success page at the merchant
     * website, the Authorization callback will fire at the same time.
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
     * This is why we reject the initial Authorization callback, unless success
     * / failure page rendering events have been logged. If they have been, then
     * there is no possibility of a race condition occurring. If they haven't
     * been, odds are that the first Authorization callback has been sent too
     * quickly, and since the order is likely being handled by the processes
     * spawned by the success / failure page, we should wait for that to finish.
     * This should never take more than a couple of seconds at worst, so
     * accepting the second attempted Authorization callback should be fine.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     */
    public static function isReady(
        CallbackInterface $callback
    ): bool {
        $successPageReached = PaymentHistoryRepository::hasExecuted(
            paymentId: $callback->getPaymentId(),
            event: Event::REACHED_ORDER_SUCCESS_PAGE
        );

        $failurePageReached = PaymentHistoryRepository::hasExecuted(
            paymentId: $callback->getPaymentId(),
            event: Event::REACHED_ORDER_FAILURE_PAGE
        );

        return
            $successPageReached ||
            $failurePageReached ||
            $callback instanceof CreditApplication ||
            self::hasReceivedFirstAuthorization(callback: $callback)
        ;
    }

    /**
     * Check if we have received the first authorization callback.
     *
     * If we have not, then use the payment history to log that we have now
     * received it if the current callback is an authorization callback.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     */
    public static function hasReceivedFirstAuthorization(
        CallbackInterface $callback
    ): bool {
        $hasExecuted = EcomPaymentHistoryRepository::hasExecuted(
            paymentId: $callback->getPaymentId(),
            event: Event::IS_READY_FOR_AUTHORIZATION
        );

        if (!$hasExecuted && $callback instanceof Authorization) {
            EcomPaymentHistoryRepository::write(
                entry: new Entry(
                    paymentId: $callback->getPaymentId(),
                    event: Event::IS_READY_FOR_AUTHORIZATION,
                    user: User::RESURSBANK
                )
            );
        }

        return $hasExecuted;
    }

    /**
     * Handle not ready state.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws HttpException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    private static function handleNotReady(
        CallbackInterface $callback
    ): void {
        if (!self::isReady(callback: $callback)) {
            throw new HttpException(
                message: Translator::translate(
                    phraseId: 'called-error-order-not-ready'
                ),
                code: 503
            );
        }
    }

    /**
     * Handle processing error.
     *
     * @throws ConfigException
     */
    private static function handleProcessingError(
        Throwable $error,
        string $paymentId
    ): int {
        self::logException(exception: $error);
        $code = 408;

        if ($error instanceof HttpException) {
            $code = $error->getCode();
        }

        self::trackError(paymentId: $paymentId, error: $error);

        return $code;
    }
}
