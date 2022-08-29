<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

class Identification extends Model
{
    public function __construct(
        public readonly string $type,
        public readonly string $reference,
    ) {
    }
}
