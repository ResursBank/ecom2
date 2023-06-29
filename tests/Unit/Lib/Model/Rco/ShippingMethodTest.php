<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\RcoRequiredFieldException;
use Resursbank\Ecom\Exception\RcoShippingScopeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Scope;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\ShippingMethod;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type;

class ShippingMethodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws IllegalValueException
     * @throws RcoRequiredFieldException
     * @throws RcoShippingScopeException
     */
    public function testBadScope(): void
    {
        $this->expectException(exception: RcoShippingScopeException::class);

        new ShippingMethod(
            methodId: 'methodId',
            name: 'name',
            scope: [],
            type: Type::PICKUP,
            description: 'description',
            price: new Price(
                display: 'shipping_string',
                calculate: 200,
                calculateTax: 25
            ),
            deliveryEta: '1 dag',
            options: [],
            required: [],
            carrier: Carrier::POSTNORD
        );
    }

    /**
     * @throws IllegalValueException
     * @throws RcoRequiredFieldException
     * @throws RcoShippingScopeException
     */
    public function testProperScope(): void
    {
        self::assertInstanceOf(
            expected: ShippingMethod::class,
            actual: new ShippingMethod(
                methodId: 'methodId',
                name: 'name',
                scope: [Scope::B2B->value],
                type: Type::PICKUP,
                description: 'description',
                price: new Price(
                    display: 'shipping_string',
                    calculate: 200,
                    calculateTax: 25
                ),
                deliveryEta: '1 dag',
                options: [],
                required: [],
                carrier: Carrier::POSTNORD
            )
        );
    }

    /**
     * @throws RcoRequiredFieldException
     * @throws RcoShippingScopeException
     * @throws IllegalValueException
     * @throws IllegalValueException
     */
    public function testBadRequiredField(): void
    {
        $this->expectException(exception: RcoRequiredFieldException::class);

        new ShippingMethod(
            methodId: 'methodId',
            name: 'name',
            scope: [Scope::B2B->value],
            type: Type::PICKUP,
            description: 'description',
            price: new Price(
                display: 'shipping_string',
                calculate: 200,
                calculateTax: 25
            ),
            deliveryEta: '1 dag',
            options: [],
            required: ['WrongValue'],
            carrier: Carrier::POSTNORD
        );
    }

    /**
     * @throws IllegalValueException
     * @throws RcoRequiredFieldException
     * @throws RcoShippingScopeException
     */
    public function testEmptyRequiredField(): void
    {
        $this->assertInstanceOf(
            expected: ShippingMethod::class,
            actual: new ShippingMethod(
                methodId: 'methodId',
                name: 'name',
                scope: [Scope::B2B->value],
                type: Type::PICKUP,
                description: 'description',
                price: new Price(
                    display: 'shipping_string',
                    calculate: 200,
                    calculateTax: 25
                ),
                deliveryEta: '1 dag',
                options: [],
                required: [],
                carrier: Carrier::POSTNORD
            )
        );
    }

    /**
     * @throws IllegalValueException
     * @throws RcoRequiredFieldException
     * @throws RcoShippingScopeException
     */
    public function testProperRequiredField(): void
    {
        $this->assertInstanceOf(
            expected: ShippingMethod::class,
            actual: new ShippingMethod(
                methodId: 'methodId',
                name: 'name',
                scope: [Scope::B2B->value],
                type: Type::PICKUP,
                description: 'description',
                price: new Price(
                    display: 'shipping_string',
                    calculate: 200,
                    calculateTax: 25
                ),
                deliveryEta: '1 dag',
                options: [],
                required: ['GOVERNMENT_ID', 'PHONE'],
                carrier: Carrier::GENERIC
            )
        );
    }
}
