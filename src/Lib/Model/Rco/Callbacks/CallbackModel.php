<?php

namespace Resursbank\Ecom\Lib\Model\Rco\Callbacks;

use Resursbank\Ecom\Lib\Model\Model;

class CallbackModel extends Model
{
    /**
     * @param string $url URL to register for the specific callback.
     * @param string $authorization The Authorization header to set when doing the callback.
     */
    public function __construct(
        private readonly string $url,
        private readonly string $authorization
    ) {
    }
}
