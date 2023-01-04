<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\UniqueSellingPoint;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;

/**
 * Unique Selling Point fetcher
 */
class Fetcher
{
    /**
     * Fetches the localized USP translation for a payment method type.
     *
     * @param Type $paymentMethodType
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalTypeException
     */
    public static function getBasicTranslation(Type $paymentMethodType): string
    {
        return Translator::translate(
            phraseId: $paymentMethodType->value,
            translationFile: __DIR__ . '/Resources/translations.json'
        );
    }
}
