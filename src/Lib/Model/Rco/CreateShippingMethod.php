<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type;

use function in_array;

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
     * @param array|null $required This is actually an array of enum values, see
     * ECP-546, currently fixed using evaluateFields to convert data.
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
        public ?array $required = null
    ) {
        $this->evaluateRequired();
    }

    /**
     * Convert anonymous strings to enum correspondent for required.
     */
    private function evaluateRequired(): void
    {
        if (!$this->required) {
            return;
        }

        $data = [];

        foreach ($this->required as $field) {
            $data[] = in_array(
                needle: $field,
                haystack: Required::cases(),
                strict: true
            ) ? $field : Required::from(value: $field);
        }

        $this->required = $data;
    }
}
