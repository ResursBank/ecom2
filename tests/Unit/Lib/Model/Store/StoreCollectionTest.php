<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Store;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Model\Store\Store;
use Resursbank\Ecom\Lib\Model\Store\StoreCollection;
use Resursbank\Ecom\Module\Store\Enum\Country;

/**
 * Unit tests for StoreCollection
 */
class StoreCollectionTest extends TestCase
{
    /**
     * Generate a collection with dummy stores.
     *
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalTypeException
     * @throws \Resursbank\Ecom\Exception\Validation\EmptyValueException
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalValueException
     */
    private function getCollection(): StoreCollection
    {
        return new StoreCollection(
            data:
            [
                new Store(
                    id: '31ecb532-2610-4972-9f09-ef7580a97ed4',
                    nationalStoreId: 1001,
                    countryCode: Country::SE,
                    name: 'Store 01',
                    organizationNumber: '112233-4455'
                ),
                new Store(
                    id: '7b5236f0-db6f-410c-8896-ad0ae66e7798',
                    nationalStoreId: 1002,
                    countryCode: Country::SE,
                    name: 'Store 02',
                    organizationNumber: '112233-4455'
                ),
                new Store(
                    id: 'e67898cb-2219-456c-927f-e75569ea3a55',
                    nationalStoreId: 1003,
                    countryCode: Country::SE,
                    name: 'Store 03',
                    organizationNumber: '112233-4455'
                )
            ]
        );
    }

    /**
     * Test that getSelectList works.
     *
     * @throws \Resursbank\Ecom\Exception\Validation\EmptyValueException
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalTypeException
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalValueException
     */
    public function testGetSelectList(): void
    {
        $stores = $this->getCollection();

        $selectList = $stores->getSelectList();

        /** @var Store $store */
        foreach ($stores->getData() as $store) {
            $this->assertArrayHasKey(key: $store->id, array: $selectList);

            if (!array_key_exists(key: $store->id, array: $selectList)) {
                continue;
            }

            $split = explode(separator: ': ', string: $selectList[$store->id]);

            $this->assertEquals(
                expected: $store->nationalStoreId,
                actual: $split[0]
            );
            $this->assertEquals(expected: $store->name, actual: $split[1]);
        }
    }

    /**
     * Test that getSingleStoreId works as intended.
     *
     * @throws \Resursbank\Ecom\Exception\Validation\EmptyValueException
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalTypeException
     * @throws \Resursbank\Ecom\Exception\Validation\IllegalValueException
     */
    public function testGetSingleStoreId(): void
    {
        $stores = $this->getCollection();
        $singleStore = new Store(
            id: '31ecb532-2610-4972-9f09-ef7580a97ed4',
            nationalStoreId: 1001,
            countryCode: Country::SE,
            name: 'Store 01',
            organizationNumber: '112233-4455'
        );
        $singleStoreCol = new StoreCollection(data: [$singleStore]);

        $this->assertNull(
            actual: $stores->getSingleStoreId()
        );
        $this->assertIsString(
            actual: $singleStoreCol->getSingleStoreId()
        );
        $this->assertEquals(
            expected: $singleStore->id,
            actual: $singleStoreCol->getSingleStoreId()
        );
    }
}
