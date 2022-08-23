<?php

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Lib\Validation\StringValidation;
use stdClass;

class Payment
{
    public function __construct(
        public readonly string $id,
        public readonly string $created,
        public readonly string $countryCode,
        public readonly string $storeId,
        public readonly string $paymentMethodId,
        private readonly StringValidation $stringValidation = new StringValidation(),
    ) {
    }
}
