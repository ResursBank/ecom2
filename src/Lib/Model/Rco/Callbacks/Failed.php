<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Callbacks;

/**
 * RCO+ Callback Failed.
 */
class Failed extends Callback
{
    /**
     * @param string $url An https url to that will be called when a payment has failed.
     * @param string $authorization The Authorization header to set when doing the callback.
     */
    public function __construct(string $url, string $authorization)
    {
        parent::__construct(url: $url, authorization: $authorization);
    }
}
