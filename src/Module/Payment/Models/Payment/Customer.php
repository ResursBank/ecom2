<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer address data from a payment.
 */
class Customer extends Model
{
    /**
     * @param Address $deliveryAddress
     * @param string $email
     * @param string $governmentId
     * @param string $mobilePhone
     * @param string $phone
     * @param string $customerType
     * @param Identification|null $identification
     */
    public function __construct(
        public readonly Address $deliveryAddress,
        public readonly string $email,
        public readonly string $governmentId,
        public readonly string $mobilePhone,
        public readonly string $phone,
        public readonly string $customerType,
        public readonly ?Identification $identification = null,
    ) {
    }
}
