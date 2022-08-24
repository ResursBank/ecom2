<?php

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Models\Payment\Application;
use Resursbank\Ecom\Module\Payment\Models\Payment\Customer;
use Resursbank\Ecom\Module\Payment\Models\Payment\Information;
use Resursbank\Ecom\Module\Payment\Models\Payment\Status;

class FindPayment
{
    /**
     * @param string $id
     * @param string $created
     * @param string $storeId
     * @param string $paymentMethodId
     * @param Customer $customer
     * @param array $paymentActions
     * @param Status $status
     * @param StringValidation $stringValidation
     * @todo Use Payment as primary class instead of this, when FindPayment has proper values in place.
     * @todo Some of them are either different from Payment, or entirely missing.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $created,
        public readonly string $storeId,
        public readonly string $paymentMethodId,
        public readonly Customer $customer,
        public readonly array $paymentActions,
        public readonly Status $status,
        private readonly StringValidation $stringValidation = new StringValidation(),
    ) {
    }
}
