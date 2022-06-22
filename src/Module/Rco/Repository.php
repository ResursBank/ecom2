<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Module\Module as CoreModule;
use Resursbank\Ecom\Module\Rco\Api\GetPayment;
use Resursbank\Ecom\Module\Rco\Api\InitPayment;
use Resursbank\Ecom\Module\Rco\Api\UpdatePayment;
use Resursbank\Ecom\Module\Rco\Api\UpdatePaymentReference;
use Resursbank\Ecom\Module\Rco\Models\GetPayment\Response;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request as InitPaymentRequest;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Response as InitPaymentResponse;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request as UpdatePaymentRequest;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Response as UpdatePaymentResponse;
use Resursbank\Ecom\Module\Rco\Models\UpdatePaymentReference\Request as UpdatePaymentReferenceRequest;
use Resursbank\Ecom\Module\Rco\Models\UpdatePaymentReference\Response as UpdatePaymentReferenceResponse;

/**
 * Main entrypoint for interfacing with the RCO API programmatically
 */
class Repository extends CoreModule
{
    public const HOSTNAME_PROD = 'checkout.resurs.com';
    public const HOSTNAME_TEST = 'omnitest.resurs.com';

    /**
     * Initialize a payment session
     *
     * @param InitPaymentRequest $request
     * @param string $orderReference
     * @return InitPaymentResponse
     * @throws ReflectionException
     */
    public static function initPayment(InitPaymentRequest $request, string $orderReference): InitPaymentResponse
    {
        return (new InitPayment())
            ->call(request: $request, orderReference: $orderReference);
    }

    /**
     * Update an existing payment session
     *
     * @param UpdatePaymentRequest $request
     * @param string $orderReference
     * @return UpdatePaymentResponse
     * @throws ReflectionException
     */
    public static function updatePayment(UpdatePaymentRequest $request, string $orderReference): UpdatePaymentResponse
    {
        return (new UpdatePayment())
            ->call(request: $request, orderReference: $orderReference);
    }

    /**
     * Update the payment reference for a payment session
     *
     * @param UpdatePaymentReferenceRequest $request
     * @param string $orderReference
     * @return UpdatePaymentReferenceResponse
     * @throws ReflectionException
     */
    public static function updatePaymentReference(
        UpdatePaymentReferenceRequest $request,
        string $orderReference
    ): UpdatePaymentReferenceResponse {
        return (new UpdatePaymentReference())
            ->call(request: $request, orderReference: $orderReference);
    }

    /**
     * Get existing payment session
     *
     * @param string $orderReference
     * @return Response
     * @throws ReflectionException
     */
    public static function getPayment(string $orderReference): Response
    {
        return (new GetPayment())
            ->call(orderReference: $orderReference);
    }

    public static function registerCallback(string $callback, string $uriTemplate): void
    {
    }

    public static function getCallbacks(): void
    {
    }

    public static function getCallback(string $callback): void
    {
    }

    public static function unregisterCallback(string $callback): void
    {
    }

    /**
     * Gets API hostname
     *
     * @return string
     */
    public static function getApiHostname(): string
    {
        if (Config::$instance->isProduction) {
            return self::HOSTNAME_PROD;
        }

        return self::HOSTNAME_TEST;
    }
}
