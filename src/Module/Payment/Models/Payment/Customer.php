<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Data\Models\Address;
use Resursbank\Ecom\Lib\Model\Model;

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
        public readonly string $customerType,
        public readonly string $email,
        public readonly string $governmentId,
        public readonly string $mobilePhone,
        public readonly ?Address $deliveryAddress = null,
        public readonly ?Identification $identification = null,
        public readonly ?string $phone = null,
    ) {
    }
}
