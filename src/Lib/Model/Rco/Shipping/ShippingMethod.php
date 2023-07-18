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
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

class ShippingMethod extends Model
{
    /**
     * @param string $methodId An unique id set by the merchant.
     * @param string $name Name of the shipping method.
     * @param Type $type Type of pickup can be: PICKUP,IN_STORE,MAILBOX,DELIVERY.
     * @param string $description Descriptive text shown to the user in the checkout.
     * @param Price $price Price model.
     * @param string $deliveryEta Description of delivery ETA.
     * @param array $options Specific shipping options.
     * @param array $required List of required fields if this method is used: GOVERNMENT_ID,EMAIL,PHONE,NAME,ADDRESS.
     * @param Carrier $carrier Can be one of predefined carriers or GENERIC for other carriers: POSTNORD,GENERIC.
     * @param array|null $scope Indicates which customer types the method should be available for. Possible val: B2C,B2B
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws RequiredFieldException
     * @throws ShippingScopeException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $methodId,
        public readonly string $name,
        public readonly Type $type,
        public readonly string $description,
        public readonly Price $price,
        public readonly string $deliveryEta,
        public readonly array $options,
        public readonly array $required,
        public readonly Carrier $carrier,
        public readonly ?array $scope = null,
        private readonly ArrayValidation $arrayValidation = new ArrayValidation(),
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateMethodID();
        $this->validateName();
        $this->validateDescription();
        $this->validateScope();
        $this->validateRequired();
    }

    /**
     * Validate array content of scope. Values allowed: B2C,B2B.
     *
     * @throws ShippingScopeException
     */
    private function validateScope(): void
    {
        if (!$this->scope) {
            return;
        }

        if (
            !in_array(
                needle: Scope::B2C,
                haystack: $this->scope,
                strict: true
            ) &&
            !in_array(needle: Scope::B2B, haystack: $this->scope, strict: true)
        ) {
            throw new ShippingScopeException(
                message: 'Scope must contain B2B or B2C to be valid.'
            );
        }
    }

    /**
     * Validate array of $this->required and allow it to only contain GOVERNMENT_ID,EMAIL,PHONE,NAME,ADDRESS.
     *
     * @throws IllegalValueException
     * @throws RequiredFieldException
     */
    private function validateRequired(): void
    {
        if (!count($this->required)) {
            return;
        }

        // If the array contains something, but not the pre-defined requirements for RCO, an exception
        // should be thrown.
        if (
            !$this->arrayValidation->inArrayMulti(
                needle: $this->required,
                haystack: [
                'GOVERNMENT_ID',
                'EMAIL',
                'PHONE',
                'NAME',
                'ADDRESS'
                ]
            )
        ) {
            throw new RequiredFieldException(
                message: 'Malicious value found in the array of required fields.'
            );
        }
    }

    /**
     * @throws EmptyValueException
     */
    private function validateMethodID(): void
    {
        $this->stringValidation->notEmpty(value: $this->methodId);
    }

    /**
     * @throws EmptyValueException
     */
    private function validateName(): void
    {
        $this->stringValidation->notEmpty(value: $this->name);
    }

    /**
     * @throws EmptyValueException
     */
    private function validateDescription(): void
    {
        $this->stringValidation->notEmpty(value: $this->description);
    }
}
