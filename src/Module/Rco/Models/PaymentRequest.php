<?php

namespace Resursbank\Ecom\Module\Rco\Models;

/**
 * Defines a payment request object
 */
class PaymentRequest
{
    public OrderItemCollection $orderLines;
    public MetaDataCollection $metaData;
    public Customer $customer;
    public string $successUrl;
    public string $backUrl;
    public string $shopUrl;
}
