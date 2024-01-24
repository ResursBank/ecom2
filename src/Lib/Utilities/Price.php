<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Translator;

/**
 * Methods to relating to price operations.
 */
class Price
{
    /**
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalTypeException
     */
    public static function format(int|float $value): string
    {
        return sprintf(
            Translator::translate(phraseId: 'price-format'),
            number_format(
                num: $value,
                decimals: 2,
                decimal_separator: '.',
                thousands_separator: ','
            )
        );
    }
}
