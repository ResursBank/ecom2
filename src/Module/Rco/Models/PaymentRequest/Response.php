<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\PaymentRequest;

class Response
{
    public function __construct(
        public readonly string $paymentSessionId,
        public readonly string $iframe,
        public readonly string $script,
        public readonly Customer $customer,
        public readonly string $baseUrl,
        public readonly string $html
    ) {
    }
}
