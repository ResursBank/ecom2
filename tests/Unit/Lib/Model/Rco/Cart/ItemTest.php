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
use Resursbank\Ecom\Lib\Utilities\Strings;
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
            $itemId = Strings::generateRandomString(length: 36);
        }

        if ($description === null) {
            $description = Strings::generateRandomString(length: 200);
        }

        if ($quantityUnit === null) {
            $quantityUnit = Strings::generateRandomString(length: 1);
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
            $url = Strings::generateRandomString(length: 1234);
        }

        if ($imageUrl === null) {
            $imageUrl = Strings::generateRandomString(length: 1123);
        }

        if ($tags === null) {
            $tags = [Strings::generateRandomString(length: 100)];
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
                Strings::generateRandomString(length: 12),
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
        DataIntegrity::testValueIntegrity(
            accepted: [
                [
                    Strings::generateRandomString(length: 1),
                    Strings::generateRandomString(length: 10),
                    Strings::generateRandomString(length: 12)
                ],
                [
                    Strings::generateRandomString(length: 150)
                ],
                [
                    Strings::generateRandomString(length: 1),
                    Strings::generateRandomString(length: 10),
                    Strings::generateRandomString(length: 100),
                    Strings::generateRandomString(length: 250),
                    Strings::generateRandomString(length: 4),
                    Strings::generateRandomString(length: 66),
                    Strings::generateRandomString(length: 154),
                    Strings::generateRandomString(length: 132),
                    Strings::generateRandomString(length: 5),
                    Strings::generateRandomString(length: 98)
                ]
            ],
            rejected: [
                [
                    Strings::generateRandomString(length: 1),
                    Strings::generateRandomString(length: 10),
                    Strings::generateRandomString(length: 100),
                    Strings::generateRandomString(length: 250),
                    Strings::generateRandomString(length: 4),
                    Strings::generateRandomString(length: 66),
                    Strings::generateRandomString(length: 154),
                    Strings::generateRandomString(length: 132),
                    Strings::generateRandomString(length: 5),
                    Strings::generateRandomString(length: 98),
                    Strings::generateRandomString(length: 100)
                ],
                [
                    Strings::generateRandomString(length: 32),
                    42
                ]
            ],
            callback: fn (array $v) => $this->generateModel(tags: $v),
            test: $this
        );
    }
}
