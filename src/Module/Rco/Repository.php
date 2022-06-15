<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco;

use Resursbank\Ecom\Module\Module as CoreModule;
use Resursbank\Ecom\Module\Rco\Models\PaymentRequest;

class Repository extends CoreModule
{
    public static function initPayment(
        PaymentRequest\Request $request
    ):PaymentRequest\Response  {
        $response = PaymentRequest\Request::create(request: $request);

        return $response;
    }

    public static function updatePayment(
        PaymentRequest\Request
    )
}
