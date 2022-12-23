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
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Module\Payment\Enum\Status;
use Resursbank\Ecom\Module\Payment\Repository;
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
    private static function getSigningUrl(
        Payment $payment
    ): string {
        if (!$payment->taskRedirectionUrls) {
            throw new EmptyValueException(
                message: 'No redirection URL object found'
            );
        }

        if ($payment->customer->governmentId === null) {
            throw new EmptyValueException(message: 'No government ID found');
        }

        $url = '';
        $elapsed = 0;

        while (!str_contains(haystack: $url, needle: 'authenticate')) {
            if ($elapsed >= 10) {
                throw new RuntimeException(
                    message: sprintf(
                        'Timeout waiting for signing URL (got %s).',
                        $url
                    )
                );
            }

            $curl = new Curl(
                url: $payment->taskRedirectionUrls->customerUrl,
                requestMethod: RequestMethod::GET,
                contentType: ContentType::URL,
                authType: AuthType::NONE,
                responseContentType: ContentType::RAW
            );
            $curl->exec();

            $elapsed++;

            $url = $curl->getEffectiveUrl();
        }

        return str_replace(
            search: 'authenticate',
            replace: 'doAuth',
            subject: $url
        ) . '&govId=' . $payment->customer->governmentId;
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
        $curl = new Curl(
            url: self::getSigningUrl(payment: $payment),
            requestMethod: RequestMethod::GET,
            contentType: ContentType::EMPTY,
            authType: AuthType::NONE,
            responseContentType: ContentType::RAW
        );
        $curl->exec();

        // Wait for the payment to be processed at Resurs Bank.
        self::waitForStatusUpdate(payment: $payment);
    }
}
