<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Module\Payment\Enum\Status;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\Rco\Repository as RcoRepository;
use RuntimeException;

use function sleep;
use function sprintf;

/**
 * Handles mock signing in dev.
 */
class MockSigner
{
    /**
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @throws IllegalValueException
     */
    // phpcs:ignore
    private static function getSigningUrl(
        string $url,
        string $governmentId
    ): string {
        $attempts = 0;

        while (!str_contains(haystack: $url, needle: 'authenticate')) {
            $attempts++;

            if ($attempts >= 10) {
                throw new RuntimeException(
                    message: sprintf(
                        'Timeout waiting for signing URL (got %s).',
                        $url
                    )
                );
            }

            $curl = new Curl(
                url: $url,
                requestMethod: RequestMethod::GET,
                contentType: ContentType::URL,
                authType: AuthType::NONE,
                responseContentType: ContentType::RAW
            );

            try {
                $curl->exec();

                $url = self::getEffectiveUrl(curl: $curl);
            } catch (CurlException) {
                self::handleCurlException(attempts: $attempts);
            }
        }

        return str_replace(
            search: 'authenticate',
            replace: 'doAuth',
            subject: $url
        ) . '&govId=' . $governmentId;
    }

    /**
     * Fetch CURLINFO_EFFECTIVE_URL
     */
    private static function getEffectiveUrl(Curl $curl): string
    {
        return (string) curl_getinfo(
            handle: $curl->ch,
            option: CURLINFO_EFFECTIVE_URL
        );
    }

    /**
     * Log error and sleep for 500 ms.
     *
     * @throws ConfigException
     */
    private static function handleCurlException(int $attempts): void
    {
        Config::getLogger()->error(
            message: 'CurlException caught on attempt number ' . $attempts .
            ', retrying again in 500 ms.'
        );
        usleep(microseconds: 500000);
    }

    /**
     * Continuously poll payment status until it matches the expected status.
     * Waits a maximum of 10 seconds before throwing an exception.
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
    private static function waitForStatusUpdate(
        Payment $payment
    ): void {
        $elapsed = 0;

        while ($payment->status !== Status::ACCEPTED) {
            if ($elapsed >= 30) {
                throw new RuntimeException(
                    message: sprintf(
                        'Timeout waiting for payment status %s. Current status is %s',
                        Status::ACCEPTED->value,
                        $payment->status->value
                    )
                );
            }

            sleep(seconds: 1);
            $elapsed++;

            $payment = Repository::get(paymentId: $payment->id);
        }
    }

    /**
     * Continuously poll payment status until it matches the expected status.
     * Waits a maximum of 10 seconds before throwing an exception.
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
    private static function waitForStatusUpdateRco(
        Checkout $checkout
    ): void {
        $elapsed = 0;

        /* PAID indicates that the checkout session has been completed, it does
           not necessarily mean that the payment has been captured. */
        while ($checkout->status->type !== CheckoutStatus::PAID) {
            if ($elapsed >= 30) {
                throw new RuntimeException(
                    message: sprintf(
                        'Timeout waiting for payment status %s. Current status is %s',
                        CheckoutStatus::PAID->value,
                        $checkout->status->type->value
                    )
                );
            }

            sleep(seconds: 1);
            $elapsed++;

            $checkout = RcoRepository::get(id: $checkout->id);
        }
    }

    /**
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
    public static function approve(Payment $payment): void
    {
        if (!$payment->taskRedirectionUrls) {
            throw new EmptyValueException(
                message: 'No redirection URL object found'
            );
        }

        if ($payment->customer->governmentId === null) {
            throw new EmptyValueException(message: 'No government ID found');
        }

        $curl = new Curl(
            url: self::getSigningUrl(
                url: $payment->taskRedirectionUrls->customerUrl,
                governmentId: $payment->customer->governmentId
            ),
            requestMethod: RequestMethod::GET,
            contentType: ContentType::EMPTY,
            authType: AuthType::NONE,
            responseContentType: ContentType::RAW
        );
        $curl->exec();

        // Wait for the payment to be processed at Resurs Bank.
        self::waitForStatusUpdate(payment: $payment);
    }

    /**
     * @param string $ssn Cannot get from Checkout instance, value is masked.
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
    public static function approveRcoPayment(
        Checkout $checkout,
        string $ssn
    ): void {
        if (!isset($checkout->status->location->url)) {
            throw new EmptyValueException(message: 'No redirection URL found.');
        }

        if (!isset($checkout->customer->governmentId)) {
            throw new EmptyValueException(message: 'No government ID found.');
        }

        if (!is_numeric(value: substr(string: $ssn, offset: 0, length: 2))) {
            $ssn = substr(string: $ssn, offset: 2);
        }

        $curl = new Curl(
            url: self::getSigningUrl(
                url: $checkout->status->location->url,
                governmentId: $ssn
            ),
            requestMethod: RequestMethod::GET,
            contentType: ContentType::EMPTY,
            authType: AuthType::NONE,
            responseContentType: ContentType::RAW
        );
        $curl->exec();

        self::waitForStatusUpdateRco(
            checkout: RcoRepository::get(id: $checkout->id)
        );
    }
}
