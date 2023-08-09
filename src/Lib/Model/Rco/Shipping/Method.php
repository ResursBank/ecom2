<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Exception\Rco\RequiredFieldException;
use Resursbank\Ecom\Exception\Rco\ShippingScopeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use function in_array;

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
     * @param OptionCollection $options Specific shipping options.
     * @param array $required This is actually an array of enum values, see
     * ECP-546, currently fixed using evaluateFields to convert data.
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
        public array $required
    ) {
        $this->evaluateRequired();
    }

    /**
     * Convert anonymous strings to enum correspondent for required.
     */
    private function evaluateRequired(): void
    {
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
