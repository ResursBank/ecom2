<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Customer\DeviceInfo;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\DeliveryAddress;

/**
 * Customer address data from a payment.
 */
class Customer extends Model
{
    public function __construct(
        public readonly ?DeliveryAddress $deliveryAddress,
        public readonly ?CustomerType $customerType,
        public readonly ?string $contactPerson,

        /**
         * @todo Don't know how to validate email.
         */
        public readonly string $email,

        /**
         * @todo Don't know how to validate governmentId.
         */
        public readonly ?string $governmentId,

        /**
         * @todo Don't know how to validate phone number.
         */
        public readonly ?string $mobilePhone,
        public readonly ?DeviceInfo $deviceInfo,
    ) {
        
    }
    
    private function validateContactPerson(): void
    {
        
    }
}
