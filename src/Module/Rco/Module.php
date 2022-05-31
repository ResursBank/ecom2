<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use Resursbank\Ecom\Module\Module as CoreModule;
use Resursbank\Ecom\Module\Rco\Models\PaymentRequest;

class Module extends CoreModule
{
    public static function initializePayment(
        PaymentRequest $request
    ):void  {
    }
}
