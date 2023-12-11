<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Cart;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Collection of order row items in RCO+ GET requests.
 */
class ItemCollection extends Collection
{
    /**
     * @param array $data
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: Item::class);
    }

    /**
     * @param string $itemId
     * @return Item|null
     */
    public function getByItemId(string $itemId): ?Item
    {
        /** @var Item $item */
        foreach ($this->getData() as $item) {
            if ($item->itemId === $itemId) {
                return $item;
            }
        }

        return null;
    }
}
