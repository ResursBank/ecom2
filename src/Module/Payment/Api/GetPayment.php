<?php

namespace Resursbank\Ecom\Module\Payment\Api;

use Resursbank\Ecom\Lib\Api\Mapi;

class GetPayment
{
    /**
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
    }
}
