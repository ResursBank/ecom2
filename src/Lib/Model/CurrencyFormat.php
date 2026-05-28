<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

/**
 * Currency formats
 */
enum CurrencyFormat
{
    case SYMBOL_FIRST;
    case SYMBOL_LAST;
}
