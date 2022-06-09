<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models;

/**
 * Defines a payment session
 */
class PaymentSession
{
    public string $paymentSessionId;
    public string $iframe;
    public string $script;
    public Customer $customer;
    public string $baseUrl;
    public string $html;
}
