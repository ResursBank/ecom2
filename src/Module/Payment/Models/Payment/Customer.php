<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

class Customer extends Model
{
    /**
     * @param Address $deliveryAddress
     * @param Identification $identification
     * @param string $email
     * @param string $governmentId
     * @param string $mobilePhone
     * @param string $phone
     * @param mixed $customerType
     * @todo Make sure customerType is always returning proper information.
     * @todo customerType empty used to be both types. But swagger says that it also can be an array.
     */
    public function __construct(
        public readonly Address $deliveryAddress,
        public readonly Identification $identification,
        public readonly string $email,
        public readonly string $governmentId,
        public readonly string $mobilePhone,
        public readonly string $phone,
        public readonly mixed $customerType = null,
    ) {
    }
}
