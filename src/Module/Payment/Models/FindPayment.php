<?php

namespace Resursbank\Ecom\Module\Payment\Models;

class FindPayment
{
    public function __construct(
        public readonly string $id,
        public readonly string $created,
        public readonly string $storeId,
    ) {
    }
}
