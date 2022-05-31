<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models;

class Customer
{
    public string $governmentId;
    public string $mobile;
    public string $email;
    public Address $deliveryAddress;
}
