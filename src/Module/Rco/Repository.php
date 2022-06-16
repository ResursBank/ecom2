<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use Resursbank\Ecom\Module\Module as CoreModule;
use Resursbank\Ecom\Module\Rco\Models\InitPayment;

class Repository extends CoreModule
{
    public static function initPayment(
        InitPayment\Request $request
    ):InitPayment\Response  {
        return  InitPayment\Request::initPayment(request: $request);
    }

    public static function updatePayment(
        InitPayment\Request
    ) {
    }
    
    public static function updatePaymentReference(
        
    )
}
