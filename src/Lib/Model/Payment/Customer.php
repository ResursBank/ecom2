<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Data\Models\Address;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Order\CustomerType;

/**
 * Customer address data from a payment.
 */
class Customer extends Model
{
    /**
     * @param string $customerType
     * @param string $email
     * @param string $governmentId
     * @param string $mobilePhone
     * @param Address|null $deliveryAddress Delivery address can be unset in some occasions.
     * @param Identification|null $identification
     * @param string|null $phone
     */
    public function __construct(
        public readonly CustomerType $customerType = CustomerType::NATURAL,
        public readonly ?string $email = null,
        public readonly ?string $governmentId = null,
        public readonly ?string $mobilePhone = null,
        public readonly ?Address $deliveryAddress = null,
        public readonly ?Identification $identification = null,
        public readonly ?string $phone = null,
    ) {
    }
}
