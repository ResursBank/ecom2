<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

/**
 * Delivery address model for RCO+. Extends Billing since they are identical.
 */
class Delivery extends Billing
{
    /**
     * Inherited constructor.
     *
     * @param string $name The full name of the recipient person or organization.
     * @param Contact $contact Contact class.
     * @param Address $address Address class.
     */
    public function __construct(string $name, Contact $contact, Address $address)
    {
        parent::__construct(name: $name, contact: $contact, address: $address);
    }
}
