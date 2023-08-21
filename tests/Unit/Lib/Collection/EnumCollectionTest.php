<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Collection;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\EnumCollection;
use Resursbank\EcomTest\Data\Enum\Car;
use Resursbank\EcomTest\Data\Enum\Furniture;
use Resursbank\EcomTest\Data\Enum\Trash;
use ValueError;

/**
 * Verifies that the EnumCollection class works as intended.
 */
final class EnumCollectionTest extends TestCase
{
    /**
     * @throws ValueError
     * @throws IllegalTypeException
     */
    private function generateCollection(
        array $data,
        string $type = Trash::class
    ): EnumCollection {
        return new EnumCollection(data: $data, type: $type);
    }

    /**
     * Assert accepted / rejected values.
     *
     * @throws IllegalTypeException
     */
    public function testData(): void
    {
        // Enum cases.
        $this->assertSame(
            expected: Furniture::cases(),
            actual: $this->generateCollection(
                data: Furniture::cases(),
                type: Furniture::class
            )->getData()
        );

        // Strings matching enum cases.
        $this->assertSame(
            expected: [Trash::FOOD, Trash::PAPER],
            actual: $this->generateCollection(
                data: [Trash::FOOD->value, Trash::PAPER->value]
            )->getData()
        );

        // Accept mixed value with matching enum cases and strings.
        $this->assertSame(
            expected: [Trash::FOOD, Trash::PAPER, Trash::PLASTIC],
            actual: $this->generateCollection(
                data: Trash::cases()
            )->getData()
        );

        // None-backed enums are rejected.
        try {
            $this->generateCollection(data: Car::cases(), type: Car::class);
            $this->fail(message: 'None-backed enum should have been rejected.');
        } catch (IllegalTypeException) {
            $this->addToAssertionCount(count: 1);
        }

        // Reject strings not matching enum cases.
        try {
            $this->generateCollection(data: ['GOLD']);
            $this->fail(message: 'GOLD should have been rejected for Trash.');
        } catch (ValueError) {
            $this->addToAssertionCount(count: 1);
        }

        // Reject mixed value with matching enum cases and none matching strings.
        try {
            $this->generateCollection(data: [Trash::PLASTIC, 'GOLD']);
            $this->fail(message: 'GOLD should have been rejected for Trash.');
        } catch (ValueError) {
            $this->addToAssertionCount(count: 1);
        }

        // Reject none matching enum values.
        try {
            $this->generateCollection(
                data: [Trash::PLASTIC],
                type: Furniture::class
            );
            $this->fail(
                message: 'PLASTIC Trash should have been rejected Furniture.'
            );
        } catch (IllegalTypeException) {
            $this->addToAssertionCount(count: 1);
        }
    }
}
