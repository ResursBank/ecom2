<?php

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Module\Rco\Models\MetaData;

class FindPayment extends Payment
{
    public function __construct(
        public readonly string $countryCode = '',
    ) {
    }
}
