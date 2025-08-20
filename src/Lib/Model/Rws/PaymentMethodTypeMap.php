<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rws;

use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Map of payment method type from RWS against payment method from MAPI.
 */
class PaymentMethodTypeMap extends Model
{
    public function __construct(
        #[StringIsUuid] public readonly string $paymentMethodId,
        public readonly PaymentMethodType $type,
    ) {
        parent::__construct();
    }
}
