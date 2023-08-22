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
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Module\Rco\Repository as RcoRepository;

use function sleep;
use function sprintf;

/**
 * Handles mock signing in integration of RCO payments.
 */
class MockSignerRco extends MockSigner
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
     * @noinspection PhpMissingParentCallCommonInspection
     */
    // phpcs:ignore
    private static function getSigningUrl(
        Checkout $checkout,
        string $governmentId
    ): string {
        $attempts = 0;
        $signingUrl = '';

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
                $checkout->status->location?->url !== null
            ) {
                $signingUrl = self::callCustomerUrl(
                    url: $checkout->status->location->url,
                    attemptCount: $attempts
                );
            }

            sleep(seconds: 1);
            $checkout = RcoRepository::get(id: $checkout->id);
            $attempts++;
        }

        return self::translateSigningUrl(
            url: $signingUrl,
            governmentId: $governmentId
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
    private static function waitForStatusUpdateRco(
        Checkout $checkout
    ): void {
        $elapsed = 0;

        /* PAID indicates that the checkout session has been completed, it does
           not necessarily mean that the payment has been captured. */
        while ($checkout->status->type !== CheckoutStatus::PAID) {
            if ($elapsed >= 10) {
                throw new ApiException(
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
    public static function approveRco(
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
                checkout: $checkout,
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
