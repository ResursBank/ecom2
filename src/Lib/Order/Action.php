<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpCSValidationInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Order;

/**
 * Possible order actions.
 */
enum Action: string
{
    case PARTIAL_CAPTURE = 'PARTIAL_CAPTURE';
    case CAPTURE = 'CAPTURE';
    case PARTIAL_REFUND = 'PARTIAL_REFUND';
    case REFUND = 'REFUND';
    case PARTIAL_CANCEL = 'PARTIAL_CANCEL';
    case CANCEL = 'CANCEL';
}
