<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Callbacks;

/**
 * RCO+ Authorized callback.
 */
class Authorized extends CallbackModel
{
    /**
     * @param string $url An https url to that will be called when a payment has been authorized.
     * @param string $authorization The Authorization header to set when doing the callback.
     */
    public function __construct(string $url, string $authorization)
    {
        parent::__construct(url: $url, authorization: $authorization);
    }
}
