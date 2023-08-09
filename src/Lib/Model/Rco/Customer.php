<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;

/**
 * Implementation of CustomerDto object.
 */
class Customer extends Model
{
    /**
     * @param Type $type Customer type enum.
     * @param string|null $governmentId Government id supplied by the customer.
     * @param Recipient|null $billing Billing address object.
     * @param Recipient|null $delivery Delivery address object.
     */
    public function __construct(
        public readonly Type $type,
        public readonly ?string $governmentId = null,
        public readonly ?Recipient $billing = null,
        public readonly ?Recipient $delivery = null
    ) {
    }
}
