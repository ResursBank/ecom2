<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;

/**
 * Implementation of ShippingMethodDto object.
 */
class Method extends Model
{
    /**
     * @param string $methodId An unique id set by the merchant.
     * @param string $name Name of the shipping method.
     * @param Type $type Type of pickup can be: PICKUP,IN_STORE,MAILBOX,DELIVERY.
     * @param Carrier $carrier Can be one of predefined carriers or GENERIC for other carriers: POSTNORD,GENERIC.
     * @param string $description Descriptive text shown to the user in the checkout.
     * @param Price $price Price model.
     * @param string $deliveryEta Description of delivery ETA.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $methodId,
        public readonly string $name,
        public readonly Type $type,
        public readonly Carrier $carrier,
        public readonly string $description,
        public readonly Price $price,
        public readonly string $deliveryEta,
        public readonly OptionCollection $options,
        public readonly RequiredCollection $required
    ) {
    }
}
