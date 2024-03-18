<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Enum;

/**
 * Enum for product types in RCO+, utilized by Item objects inside various Cart
 * objects, such as Rco\Cart and Rco\CreateCart.
 */
enum CartItemType: string
{
    case PRODUCT = 'PRODUCT';
    case SHIPPING_FEE = 'SHIPPING_FEE';
    case DISCOUNT = 'DISCOUNT';
    case DISCOUNT_CODE = 'DISCOUNT_CODE';
    case PAYMENT_FEE = 'PAYMENT_FEE';
    case GIFT_CARD = 'GIFT_CARD';
    case OTHER_PAYMENT = 'OTHER_PAYMENT';
    case GENERIC = 'GENERIC';
}
