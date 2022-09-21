<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpCSValidationInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Order;

/**
 * Defines the payment action types.
 */
enum PaymentActionType: string
{
    case CREATE = 'CREATE';
    case MODIFY_ORDER = 'MODIFY_ORDER';
    case CAPTURE = 'CAPTURE';
    case REFUND = 'REFUND';
    case CANCEL = 'CANCEL';
}
