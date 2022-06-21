<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use ReflectionException;
use Resursbank\Ecom\Module\Module as CoreModule;
use Resursbank\Ecom\Module\Rco\Api\InitPayment;
use Resursbank\Ecom\Module\Rco\Api\UpdatePayment;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request as InitPaymentRequest;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Response as InitPaymentResponse;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request as UpdatePaymentRequest;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Response as UpdatePaymentResponse;

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
    public function initPayment(InitPaymentRequest $request, string $orderReference): InitPaymentResponse
    {
        return (new InitPayment(config: $this->config))
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
    public function updatePayment(UpdatePaymentRequest $request, string $orderReference): UpdatePaymentResponse
    {
        return (new UpdatePayment(config: $this->config))
            ->call(request: $request, orderReference: $orderReference);
    }

    public function updatePaymentReference(): void
    {
    }

    public function getPayment(string $orderReference): void
    {
    }

    public function registerCallback(string $callback, string $uriTemplate): void
    {
    }

    public function getCallbacks(): void
    {
    }

    public function getCallback(string $callback): void
    {
    }

    public function unregisterCallback(string $callback): void
    {
    }
}
