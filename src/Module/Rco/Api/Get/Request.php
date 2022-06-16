<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api\Get;

use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Response;
use Resursbank\Ecom\Module\Rco\Models\InitPayment\Request;

class Request
{
    public function initPayment(Request $request): Response
    {
        $curl = new Curl();
    }
}