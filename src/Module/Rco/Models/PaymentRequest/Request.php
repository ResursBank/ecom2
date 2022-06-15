<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\PaymentRequest;

use Resursbank\Ecom\Module\Rco\Models\MetaDataCollection;

/**
 * Defines a payment request object
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Request
{
    public function __construct(
        public OrderLineCollection $orderLines,
        public MetaDataCollection $metaData,
        public Customer $customer,
        public string $successUrl,
        public string $backUrl,
        public string $shopUrl,
        public ?string $paymentCreatedCallbackUrl = null
    ) {
    }

    public static function initPayment(Request $request): Response
    {

    }
}
