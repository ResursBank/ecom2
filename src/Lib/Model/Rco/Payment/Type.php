<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

/**
 * Enum for product types in RCO+.
 */
enum Type: string
{
    case PRODUCT = 'PRODUCT';
    case SHIPPING = 'SHIPPING';
    case FEE = 'FEE';
    case DISCOUNT = 'DISCOUNT';
    case DISCOUNT_CODE = 'DISCOUNT_CODE';
    case PAYMENT_FEE = 'PAYMENT_FEE';
    case GIFT_CARD = 'GIFT_CARD';
    case OTHER_PAYMENT = 'OTHER_PAYMENT';
    case GENERIC = 'GENERIC';
}
