<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethod\Price;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type;

/**
 * Implementation of CreateShippingMethodDto object.
 */
class CreateShippingMethod extends Model
{
    /**
     * @param string $methodId An unique id set by the merchant.
     * @param string $name Name of the shipping method.
     * @param Type $type Type of pickup can be: PICKUP,IN_STORE,MAILBOX,DELIVERY.
     * @param Carrier|null $carrier Can be one of predefined carriers or GENERIC for other carriers: POSTNORD,GENERIC.
     * @param string|null $description Descriptive text shown to the user in the checkout.
     * @param Price|null $price Price model.
     * @param string|null $deliveryEta Description of delivery ETA.
     * @param OptionCollection|null $options Specific shipping options.
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        #[StringLength(min: 1, max: 128)] public readonly string $methodId,
        #[StringLength(min: 1, max: 128)] public readonly string $name,
        #[ArraySize(min: 1, max: 32)] public readonly array $scope,
        public readonly Type $type,
        public readonly ?Carrier $carrier = null,
        #[StringLength(
            min: 0,
            max: 280
        )] public readonly ?string $description = null,
        public readonly ?Price $price = null,
        #[StringLength(
            min: 0,
            max: 256
        )] public readonly ?string $deliveryEta = null,
        #[CollectionSize(
            min: 0,
            max: 32
        )] public readonly ?OptionCollection $options = null,
        #[CollectionSize(
            min: 0,
            max: 10
        )] public readonly ?RequiredCollection $required = null
    ) {
        parent::__construct();
    }
}
