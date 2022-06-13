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
 */
class Request
{
    public function __construct(
        public OrderLineCollection $orderLines,
        public MetaDataCollection $metaData,
        public Customer $customer,
        public string $successUrl,
        public string $backUrl,
        public ?string $paymentCreatedCallbackUrl,
        public string $shopUrl
    ) {
    }
}
