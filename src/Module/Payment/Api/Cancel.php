<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api;

use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Module\Payment\Models\OrderLineCollection;
use Resursbank\Ecom\Module\Payment\Models\Payment;

/**
 * POST /payments/{payment_id}/cancel
 */
class Cancel
{
    /** @var Mapi  */
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
    }


    public function call(
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null
    ): Payment
    {
        $payload = [];
        if ($orderLines) {
            $payload['orderLines'] = $orderLines->toArray();
        }
        if ($creator) {
            $payload['creator'] = $creator;
        }
    }
}
