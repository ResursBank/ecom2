<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use Resursbank\Ecom\Module\Module as CoreModule;
use Resursbank\Ecom\Module\Rco\Api\InitPayment;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request as InitPaymentRequest;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Response as InitPaymentResponse;

/**
 * Main entrypoint for interfacing with the RCO API programmatically
 */
class Repository extends CoreModule
{
    /**
     * Initialize a payment session
     *
     * @param InitPaymentRequest $request
     * @param string $orderReference
     * @return InitPaymentResponse
     * @throws \ReflectionException
     */
    public function initPayment(
        InitPaymentRequest $request,
        string $orderReference
    ): InitPaymentResponse  {
        return InitPayment::call(request: $request, orderReference: $orderReference);
    }

    /**
     * Update an existing payment session
     *
     * @param UpdatePayment\Request $request
     * @return void
     */
    public function updatePayment(
        UpdatePayment\Request $request
    ): void {
    }

    public function updatePaymentReference(): void
    {
    }
}
