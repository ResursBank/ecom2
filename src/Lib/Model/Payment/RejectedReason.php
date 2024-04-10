<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Payment\Enum\RejectedReasonCategory;

class RejectedReason extends Model
{
    public function __construct(
        public readonly ?RejectedReasonCategory $category = null
    ) {
    }
}
