<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\InitPayment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Rco\Models\Address;
use Resursbank\Ecom\Module\Rco\Models\CustomerType;

/**
 * Defines an InitPayment customer object
 */
class Customer extends Model
{
    public function __construct(
        public ?string $governmentId,
        public ?string $mobile,
        public ?string $email,
        public ?Address $deliveryAddress = null,
        public ?Address $invoiceAddress = null,
        public ?string $customerType = null,
        public ?string $mobileNotValidated = null,
        public ?string $emailNotValidated = null
    ) {
}
}
