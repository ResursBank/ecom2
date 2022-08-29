<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

class Information extends Model
{
    public function __construct(
        public readonly string $creator,
    ) {
    }
}
