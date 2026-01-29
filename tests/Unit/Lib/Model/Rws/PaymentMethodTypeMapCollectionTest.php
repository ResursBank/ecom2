<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rws;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodType;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodTypeMap;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethodTypeMapCollection;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Tests for the PaymentMethodTypeMapCollection class.
 */
class PaymentMethodTypeMapCollectionTest extends TestCase
{
    /**
     * Verify that the correct type is returned by getTypeById.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testGetTypeById(): void
    {
        $id = Strings::getUuid();

        $typeMapCollection = new PaymentMethodTypeMapCollection(
            data: [
                new PaymentMethodTypeMap(
                    paymentMethodId: Strings::getUuid(),
                    type: PaymentMethodType::GENERIC
                ),
                new PaymentMethodTypeMap(
                    paymentMethodId: $id,
                    type: PaymentMethodType::RESURS_CARD
                ),
                new PaymentMethodTypeMap(
                    paymentMethodId: Strings::getUuid(),
                    type: PaymentMethodType::RESURS_INVOICE
                )
            ]
        );

        $this->assertEquals(
            expected: PaymentMethodType::RESURS_CARD,
            actual: $typeMapCollection->getTypeById(methodId: $id)
        );
    }

    /**
     * Verify that getTypeById returns null if ID is not found.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetTypeByIdFailure(): void
    {
        $typeMapCollection = new PaymentMethodTypeMapCollection(
            data: [
                new PaymentMethodTypeMap(
                    paymentMethodId: Strings::getUuid(),
                    type: PaymentMethodType::GENERIC
                ),
                new PaymentMethodTypeMap(
                    paymentMethodId: Strings::getUuid(),
                    type: PaymentMethodType::RESURS_CARD
                ),
                new PaymentMethodTypeMap(
                    paymentMethodId: Strings::getUuid(),
                    type: PaymentMethodType::RESURS_INVOICE
                )
            ]
        );

        $this->assertEquals(
            expected: null,
            actual: $typeMapCollection->getTypeById(
                methodId: Strings::getUuid()
            )
        );
    }
}
