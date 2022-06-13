<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\PaymentRequest;

use Resursbank\Ecom\Module\Rco\Models\Address;
use Resursbank\Ecom\Module\Rco\Models\CustomerType;

class Customer
{
    public string $governmentId;
    public string $mobile;
    public string $email;
    public ?Address $deliveryAddress;
    public ?Address $invoiceAddress;
    public ?string $customerType;
    public ?string $mobileNotValidated;
    public ?string $emailNotValidated;
}
