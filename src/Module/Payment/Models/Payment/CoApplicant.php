<?php

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

class CoApplicant extends Model
{
    public function __construct(
        public readonly string $governmentId,
        public readonly string $mobilePhone,
        public readonly string $phone,
        public readonly string $email,
        public readonly ?Identification $identification = null
    ) {
    }
}
