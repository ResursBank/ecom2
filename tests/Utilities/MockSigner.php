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
use Resursbank\Ecom\Lib\Model\Payment\TaskStatusDetails;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Module\Payment\Enum\Status;
use Resursbank\Ecom\Module\Payment\Repository;

use function sleep;
use function sprintf;

/**
 * Handles mock signing in dev.
 */
class MockSigner
{
    /**
     * Fetch CURLINFO_EFFECTIVE_URL
     */
    protected static function getEffectiveUrl(Curl $curl): string
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
    protected static function handleCurlException(int $attempts): void
    {
        Config::getLogger()->error(
            message: 'CurlException caught on attempt number ' . $attempts .
            ', retrying again in 500 ms.'
        );
        usleep(microseconds: 500000);
    }

    /**
     * Call customer URL to resolve the signing URL (customer URl will lead to
     * a series are redirects, eventually landing on the signing page, thus
     * providing us with the actual URL to perform signing).
     *
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws ValidationException
     */
    protected static function callCustomerUrl(
        string $url,
        int $attemptCount
    ): string {
        $result = '';

        $curl = new Curl(
            url: $url,
            requestMethod: RequestMethod::GET,
            contentType: ContentType::URL,
            authType: AuthType::NONE,
            responseContentType: ContentType::RAW
        );

        try {
            $curl->exec();

            $result = self::getEffectiveUrl(curl: $curl);
        } catch (CurlException) {
            self::handleCurlException(attempts: $attemptCount);
        }

        return $result;
    }

    /**
     * Replace 'authenticate' in signing URL with 'doAuth' to mock signing.
     */
    protected static function translateSigningUrl(
        string $url,
        string $governmentId
    ): string {
        return str_replace(
            search: 'authenticate',
            replace: 'doAuth',
            subject: $url
        ) . '&govId=' . $governmentId;
    }

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
        Payment $payment,
        TaskStatusDetails $taskStatusDetails
    ): string {
        $attempts = 0;
        $signingUrl = '';

        if ($payment->customer->governmentId === null) {
            throw new EmptyValueException(message: 'No government ID found');
        }

        while (!str_contains(haystack: $signingUrl, needle: 'authenticate')) {
            if ($attempts >= 10) {
                throw new ApiException(
                    message: sprintf(
                        'Timeout waiting for signing URL (got %s).',
                        $signingUrl
                    )
                );
            }

            if (
                $taskStatusDetails->customer?->customerUrl !== null
            ) {
                $signingUrl = self::callCustomerUrl(
                    url: $taskStatusDetails->customer->customerUrl,
                    attemptCount: $attempts
                );
            }

            sleep(seconds: 1);
            $taskStatusDetails = Repository::getTaskStatusDetails(
                paymentId: $payment->id
            );
            $attempts++;
        }

        return self::translateSigningUrl(
            url: $signingUrl,
            governmentId: $payment->customer->governmentId
        );
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
            if ($elapsed >= 10) {
                throw new ApiException(
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
        $url = self::getSigningUrl(
            taskStatusDetails: Repository::getTaskStatusDetails(
                paymentId: $payment->id
            ),
            payment: $payment
        );

        // Try 10 times to resolve signing URL.
        $curl = new Curl(
            url: $url,
            requestMethod: RequestMethod::GET,
            contentType: ContentType::EMPTY,
            authType: AuthType::NONE,
            responseContentType: ContentType::RAW
        );

        $curl->exec();

        self::waitForStatusUpdate(payment: $payment);
    }
}
