<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type;

/**
 * Implementation of CreateShippingMethodDto object.
 */
class CreateShippingMethod extends Model
{
    /**
     * @param string $methodId An unique id set by the merchant.
     * @param string $name Name of the shipping method.
     * @param array $scope
     * @param Type $type Type of pickup can be: PICKUP,IN_STORE,MAILBOX,DELIVERY.
     * @param Carrier|null $carrier Can be one of predefined carriers or GENERIC for other carriers: POSTNORD,GENERIC.
     * @param string|null $description Descriptive text shown to the user in the checkout.
     * @param Price|null $price Price model.
     * @param string|null $deliveryEta Description of delivery ETA.
     * @param OptionCollection|null $options Specific shipping options.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $methodId,
        public readonly string $name,
        public readonly array $scope,
        public readonly Type $type,
        public readonly ?Carrier $carrier = null,
        public readonly ?string $description = null,
        public readonly ?Price $price = null,
        public readonly ?string $deliveryEta = null,
        public readonly ?OptionCollection $options = null,
        public readonly ?RequiredCollection $required = null
    ) {
    }
}
