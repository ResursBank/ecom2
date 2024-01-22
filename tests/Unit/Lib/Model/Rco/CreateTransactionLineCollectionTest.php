<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\CreateTransactionLine;
use Resursbank\Ecom\Lib\Model\Rco\CreateTransactionLineCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Utilities\Random;

/**
 * Integrity test of RCO CreateTransactionLineCollection methods.
 */
class CreateTransactionLineCollectionTest extends TestCase
{
    /**
     * Wrapper method to generate CreateTransactionLine instance.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    private function getTransactionLine(
        int $quantity,
        int $unitPrice
    ): CreateTransactionLine {
        return new CreateTransactionLine(
            type: CartItemType::GENERIC,
            description: Random::getString(length: 50),
            itemId: Random::getString(length: 25),
            quantity: $quantity,
            unitPrice: $unitPrice,
            taxRate: 25
        );
    }

    /**
     * Assert getTotal() method returns combined value of child instances.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetTotal(): void
    {
        $single = new CreateTransactionLineCollection(data: [
            $this->getTransactionLine(quantity: 1, unitPrice: 100)
        ]);

        $this->assertSame(
            expected: 100,
            actual: $single->getTotal()
        );

        $line2 = $this->getTransactionLine(
            quantity: Random::getInt(min: 5, max: 100),
            unitPrice: Random::getInt(min: 199, max: 16205)
        );

        $multiple = new CreateTransactionLineCollection(data: [$line2]);

        $this->assertSame(
            expected: $line2->unitPrice * $line2->quantity,
            actual: $multiple->getTotal()
        );

        $mass = new CreateTransactionLineCollection(data: [
            $this->getTransactionLine(quantity: 5, unitPrice: 1255),
            $this->getTransactionLine(quantity: 10, unitPrice: 100),
            $this->getTransactionLine(quantity: 2, unitPrice: 3367)
        ]);

        $this->assertSame(
            expected: 14009,
            actual: $mass->getTotal()
        );
    }
}
