<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

class Information
{
    public function __construct(
        public readonly string $creator,
    ) {
    }
}
