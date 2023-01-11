<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Curl;

use Resursbank\Ecom\Exception\ConfigException;
use Throwable;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Locale\Translator;

/**
 * Converter to turn property names like "customer.mobilePhone" into more user-friendly strings like "mobile phone"
 */
abstract class ErrorTranslator
{
    /**
     * Gets converted and localized string
     * @param string $propertyName
     * @throws ConfigException
     */
    public static function get(string $propertyName): string
    {
        try {
            return Translator::translate(
                phraseId: $propertyName,
                translationFile: __DIR__ . '/Resources/errors.json'
            );
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
            return $propertyName;
        }
    }
}
