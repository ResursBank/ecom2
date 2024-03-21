<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;

/**
 * Methods to relating to price operations.
 */
class Price
{
    /**
     * Flexible price formatter.
     */
    public static function format(
        int|float $value,
        int $decimals = 2,
        string $decimalSeparator = ',',
        string $thousandsSeparator = ' ',
        ?string $currencySymbol = null,
        ?CurrencyFormat $currencyFormat = null
    ): string {
        $formattedAmount = number_format(
            num: $value,
            decimals: $decimals,
            decimal_separator: $decimalSeparator,
            thousands_separator: $thousandsSeparator
        );

        return $currencyFormat === CurrencyFormat::SYMBOL_FIRST ?
            $currencySymbol . $formattedAmount :
            $formattedAmount . ($currencySymbol !== null ? ' ' : '') . $currencySymbol;
    }
}
