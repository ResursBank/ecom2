<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco\Cart;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Rco\Cart\Item;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\EcomTest\Utilities\DataIntegrity;
use Throwable;

/**
 * Integrity test of Cart\Item model.
 */
class ItemTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws IllegalTypeException
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @noinspection PhpSameParameterValueInspection
     * @noinspection PhpTooManyParametersInspection
     */
    // phpcs:ignore
    private function generateModel(
        ?string $itemId = null,
        ?string $itemIdDisplay = null,
        ?string $description = null,
        ?string $quantityUnit = null,
        ?int $quantity = null,
        ?int $unitPrice = null,
        ?int $totalPrice = null,
        ?int $taxRate = null,
        ?int $totalTax = null,
        ?int $totalDiscount = null,
        ?string $url = null,
        ?string $imageUrl = null,
        ?array $tags = null,
        ?int $maxQuantity = null,
        ?bool $mutable = null
    ): void {
        if ($itemId === null) {
            $itemId = Random::getString(length: 36);
        }

        if ($itemIdDisplay === null) {
            $itemIdDisplay = Random::getString(length: 36);
        }

        if ($description === null) {
            $description = Random::getString(length: 200);
        }

        if ($quantityUnit === null) {
            $quantityUnit = Random::getString(length: 1);
        }

        if ($quantity === null) {
            $quantity = 0;
        }

        if ($unitPrice === null) {
            $unitPrice = 0;
        }

        if ($totalPrice === null) {
            $totalPrice = 0;
        }

        if ($taxRate === null) {
            $taxRate = 25;
        }

        if ($totalTax === null) {
            $totalTax = 0;
        }

        if ($totalDiscount === null) {
            $totalDiscount = 0;
        }

        if ($url === null) {
            $url = 'https://example.com/' . Random::getString(length: 12);
        }

        if ($imageUrl === null) {
            $imageUrl = 'https://example.com/' . Random::getString(length: 12);
        }

        if ($tags === null) {
            $tags = [Random::getString(length: 100)];
        }

        if ($maxQuantity === null) {
            $maxQuantity = (2 ** 31) - 1;
        }

        if ($mutable === null) {
            $mutable = false;
        }

        new Item(
            type: CartItemType::PRODUCT,
            itemId: $itemId,
            itemIdDisplay: $itemIdDisplay,
            description: $description,
            quantityUnit: $quantityUnit,
            unitPrice: $unitPrice,
            totalPrice: $totalPrice,
            quantity: $quantity,
            taxRate: $taxRate,
            totalTax: $totalTax,
            totalDiscount: $totalDiscount,
            url: $url,
            imageUrl: $imageUrl,
            tags: $tags,
            maxQuantity: $maxQuantity,
            mutable: $mutable
        );
    }

    /**
     * Test generating a valid model instance.
     */
    public function testModel(): void
    {
        try {
            $this->generateModel();
            $this->addToAssertionCount(count: 1);
        } catch (Throwable) {
            $this->fail(message: 'Failed to generate model instance.');
        }
    }

    /**
     * Verify that an IllegalTypeException is thrown for non-string tags elements.
     *
     * @throws IllegalTypeException
     */
    public function testInvalidTags(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->generateModel(
            tags: [
                Random::getString(length: 12),
                42
            ]
        );
    }

    /**
     * Assert validation rules for tags property.
     *
     * @throws Exception
     */
    public function testTagsValidation(): void
    {
        $characters = range(start: 'a', end: 'z');

        DataIntegrity::testValueIntegrity(
            accepted: [
                [
                    Random::getString(length: 1, characters: $characters),
                    Random::getString(length: 10, characters: $characters),
                    Random::getString(length: 12, characters: $characters)
                ],
                [
                    Random::getString(length: 150, characters: $characters)
                ],
                [
                    Random::getString(length: 1, characters: $characters),
                    Random::getString(length: 10, characters: $characters),
                    Random::getString(length: 100, characters: $characters),
                    Random::getString(length: 250, characters: $characters),
                    Random::getString(length: 4, characters: $characters),
                    Random::getString(length: 66, characters: $characters),
                    Random::getString(length: 154, characters: $characters),
                    Random::getString(length: 132, characters: $characters),
                    Random::getString(length: 5, characters: $characters),
                    Random::getString(length: 98, characters: $characters)
                ]
            ],
            rejected: [
                [
                    Random::getString(length: 1),
                    Random::getString(length: 10),
                    Random::getString(length: 100),
                    Random::getString(length: 250),
                    Random::getString(length: 4),
                    Random::getString(length: 66),
                    Random::getString(length: 154),
                    Random::getString(length: 132),
                    Random::getString(length: 5),
                    Random::getString(length: 98),
                    Random::getString(length: 100),
                    (float)42.4
                ],
                [
                    Random::getString(length: 32),
                    (int)42
                ]
            ],
            callback: fn (array $v) => $this->generateModel(tags: $v),
            test: $this
        );
    }
}
