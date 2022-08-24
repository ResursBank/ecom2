<?php

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Models\Payment\Application;
use Resursbank\Ecom\Module\Payment\Models\Payment\Customer;
use Resursbank\Ecom\Module\Payment\Models\Payment\Information;
use Resursbank\Ecom\Module\Payment\Models\Payment\PaymentActions;
use Resursbank\Ecom\Module\Payment\Models\Payment\Status;

class Payment
{
    /**
     * @param string $id
     * @param string $created Timestamp.
     * @param string $storeId
     * @param string $paymentMethodId
     * @param Customer $customer
     * @param array $paymentActions
     * @param Information $information
     * @param Status $status
     * @param Application $application
     * @param string $countryCode
     * @param StringValidation $stringValidation
     * @todo Application and countryCode is currently not showing in FindPayment, so to make
     * @todo FindPayment compatible with the Payment model, we are temporary setting the missing fields
     * @todo with empty defaults.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $created,
        public readonly string $storeId,
        public readonly string $paymentMethodId,
        public readonly array $paymentActions,
        public readonly Customer $customer,
        public readonly Status $status,
        public readonly Information $information,
        public readonly Application $application,
        public readonly string $countryCode = '',
        private readonly StringValidation $stringValidation = new StringValidation(),
    ) {
    }
}
