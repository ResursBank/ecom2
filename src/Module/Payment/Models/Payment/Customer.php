<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

class Customer
{
    /**
     * @param mixed $customerType
     */
    public function __construct(
        public readonly mixed $customerType,
    ) {
    }
}
