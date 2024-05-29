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
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
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
 * Handles mock signing in integration of MAPI payments.
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
     * @throws AttributeCombinationException
     * @throws AttributeCombinationException
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
     * @throws AttributeCombinationException
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
     * @throws AttributeCombinationException
     * @throws NotJsonEncodedException
     */
    private static function waitForStatusUpdate(
        Payment $payment,
        string $url
    ): void {
        $elapsed = 0;

        $firstStatus = $payment->status;

        while (
            $payment->status !== Status::ACCEPTED &&
            $payment->status === $firstStatus
        ) {
            if ($elapsed >= 10) {
                throw new ApiException(
                    message: sprintf(
                        'Timeout waiting for payment status %s. Current status is %s',
                        Status::ACCEPTED->value,
                        $payment->status->value
                    )
                );
            }

            // Try to approve in each loop.
            self::curlApprove(url: $url);
            sleep(seconds: 1);
            $elapsed++;

            $payment = Repository::get(paymentId: $payment->id);
        }

        // When first discovered status changed, not into ACCEPTED and no longer REDIRECTION, there's something wrong.
        if (
            $payment->status !== $firstStatus &&
            $payment->status !== Status::TASK_REDIRECTION_REQUIRED &&
            $payment->status !== Status::ACCEPTED
        ) {
            throw new ApiException(
                message: sprintf(
                    'Payment status %s got problem. Current status gone into %s',
                    Status::ACCEPTED->value,
                    $payment->status->value
                )
            );
        }
    }

    /**
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private static function curlApprove(string $url): void
    {
        $curl = new Curl(
            url: $url,
            requestMethod: RequestMethod::GET,
            contentType: ContentType::EMPTY,
            authType: AuthType::NONE,
            responseContentType: ContentType::RAW
        );

        $curl->exec();
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
     * @throws AttributeCombinationException
     * @throws AttributeCombinationException
     * @throws AttributeCombinationException
     * @throws AttributeCombinationException
     */
    public static function approve(Payment $payment): void
    {
        $url = self::getSigningUrl(
            taskStatusDetails: Repository::getTaskStatusDetails(
                paymentId: $payment->id
            ),
            payment: $payment
        );

        // Moved this feature for which we try 10 times to resolve/approve with the signing URL.
        // Approving each round instead of only once raises the chance for success.
        self::curlApprove(url: $url);
        self::waitForStatusUpdate(payment: $payment, url: $url);
    }
}
