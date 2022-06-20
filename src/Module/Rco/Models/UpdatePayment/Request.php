<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\UpdatePayment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Rco\Models\MetaDataCollection;

/**
 * Defines a payment request object
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Request extends Model
{
    public function __construct(
        public OrderLineCollection $orderLines
    ) {
        parent::__construct();
    }
}
