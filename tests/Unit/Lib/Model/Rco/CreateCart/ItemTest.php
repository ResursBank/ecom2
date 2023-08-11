<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco\CreateCart;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\Item;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\EcomTest\Utilities\DataIntegrity;
use Throwable;

/**
 * Integrity test of CreateCart\Item model.
 */
class ItemTest extends TestCase
{
    /**
     * Get mocked model instance.
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @noinspection PhpTooManyParametersInspection
     * @noinspection PhpSameParameterValueInspection
     */
    // phpcs:ignore
    private function generateModel(
        ?string $itemId = null,
        ?string $description = null,
        ?string $quantityUnit = null,
        ?int $quantity = null,
        ?int $unitPrice = null,
        ?int $taxRate = null,
        ?int $totalDiscount = null,
        ?string $url = null,
        ?string $imageUrl = null,
        ?array $tags = null
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

        if ($unitPrice === null) {
            $unitPrice = 0;
        }

        new Item(
            type: CartItemType::PRODUCT,
            itemId: $itemId,
            description: $description,
            quantityUnit: $quantityUnit,
            unitPrice: $unitPrice,
            quantity: $quantity,
            taxRate: $taxRate,
            totalDiscount: $totalDiscount,
            url: $url,
            imageUrl: $imageUrl,
            tags: $tags
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
     * Assert validation rules for itemId property.
     *
     * @throws Exception
     */
    public function testItemIdValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                Strings::generateRandomString(length: 1),
                Strings::generateRandomString(length: 10),
                Strings::generateRandomString(length: 36)
            ],
            rejected: [
                Strings::generateRandomString(length: 37),
                Strings::generateRandomString(length: 100)
            ],
            callback: fn (string $v) => $this->generateModel(itemId: $v),
            test: $this
        );

        DataIntegrity::testEmptyValueRejection(
            callback: fn () => $this->generateModel(itemId: ''),
            test: $this
        );
    }

    /**
     * Assert validation rules for description property.
     *
     * @throws Exception
     */
    public function testDescriptionValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                '',
                Strings::generateRandomString(length: 1),
                Strings::generateRandomString(length: 100),
                Strings::generateRandomString(length: 280)
            ],
            rejected: [
                Strings::generateRandomString(length: 281),
                Strings::generateRandomString(length: 300)
            ],
            callback: fn (string $v) => $this->generateModel(description: $v),
            test: $this
        );
    }

    /**
     * Assert validation rules for quantityUnit property.
     *
     * @throws Exception
     */
    public function testQuantityUnitValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                Strings::generateRandomString(length: 1),
                Strings::generateRandomString(length: 31),
                Strings::generateRandomString(length: 32)
            ],
            rejected: [
                Strings::generateRandomString(length: 33),
                Strings::generateRandomString(length: 45)
            ],
            callback: fn (string $v) => $this->generateModel(quantityUnit: $v),
            test: $this
        );

        DataIntegrity::testEmptyValueRejection(
            callback: fn () => $this->generateModel(quantityUnit: ''),
            test: $this
        );
    }

    /**
     * Assert validation rules for quantity property.
     *
     * @throws Exception
     */
    public function testQuantityValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [0, 1, 2568],
            rejected: [-1, -512],
            callback: fn (int $v) => $this->generateModel(quantity: $v),
            test: $this
        );
    }

    /**
     * Assert validation rules for taxRate property.
     *
     * @throws Exception
     */
    public function testTaxRateValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [0, 6, 12, 25, 100],
            rejected: [-1, -55, 101],
            callback: fn (int $v) => $this->generateModel(taxRate: $v),
            test: $this
        );
    }

    /**
     * Assert validation rules for totalDiscount property.
     *
     * @throws Exception
     */
    public function testTotalDiscount(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [0, 1, 2568],
            rejected: [-1, -512],
            callback: fn (int $v) => $this->generateModel(totalDiscount: $v),
            test: $this
        );
    }

    /**
     * Assert validation rules for url property.
     *
     * @throws Exception
     */
    public function testUrlValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                'https://somwhere.resurs.com/MyCoolPlace/whatever/whoever.html',
                'https://somwhere.resurs.com/something',
                'https://somwhere.resurs.com',
                'https://somwhere.resurs.com/hey-1/yes.php',
                'http://somwhere.resurs.com/?resource=wonky&result=bad'
            ],
            rejected: [
                'ftp://files.resurs.com',
                'htps://error.resurs.com',
                'http2://www.resurs.com',
                'http3://www.resurs.com',
                Strings::generateRandomString(length: 1),
                Strings::generateRandomString(length: 45),
                Strings::generateRandomString(length: 200)
            ],
            callback: fn (string $v) => $this->generateModel(url: $v),
            test: $this
        );
    }

    /**
     * Assert validation rules for imageUrl property.
     *
     * @throws Exception
     */
    public function testImageUrlValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                'https://somwhere.resurs.com/MyCoolPlace/whatever/whoever.jpeg',
                'https://somwhere.resurs.com/something.png',
                'https://somwhere.resurs.com/test.ico',
                'https://somwhere.resurs.com/hey-1/yes.php',
                'http://somwhere.resurs.com/?resource=wonky&result=bad',
                'http://somwhere.resurs.com/somewhat'
            ],
            rejected: [
                'ftp://files.resurs.com',
                'htps://error.resurs.com',
                'http2://www.resurs.com',
                'http3://www.resurs.com',
                Strings::generateRandomString(length: 1),
                Strings::generateRandomString(length: 45),
                Strings::generateRandomString(length: 200)
            ],
            callback: fn (string $v) => $this->generateModel(imageUrl: $v),
            test: $this
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
                [],
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
                ]
            ],
            callback: fn (array $v) => $this->generateModel(tags: $v),
            test: $this
        );
    }
}
