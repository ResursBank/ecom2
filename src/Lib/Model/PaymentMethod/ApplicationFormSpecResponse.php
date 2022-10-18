<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponseCollection;

class ApplicationFormSpecResponse extends Model
{
    public function __construct(
        public readonly ?ApplicationFormSpecElementResponseCollection $elements = null
    ) {
    }
}
