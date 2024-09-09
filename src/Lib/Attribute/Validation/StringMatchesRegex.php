<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Traits\TranslatifyPropertyName;
use Resursbank\Ecom\Lib\Locale\Translator;

use function preg_match;

/**
 * Used for regex validation of strings.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringMatchesRegex
{
    use TranslatifyPropertyName;

    /**
     * @param string $pattern Regex pattern the property value has to match.
     */
    public function __construct(
        private readonly string $pattern
    ) {
    }

    /**
     * @throws IllegalCharsetException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        if (!preg_match(pattern: $this->pattern, subject: $value)) {
            throw new IllegalCharsetException(
                message: $name . ' value ' . $value . ' does not match ' .
                    $this->pattern,
                friendlyMessage: str_replace(
                    search: '%1',
                    replace: Translator::translate(
                        phraseId: $this->convert(propertyName: $name)
                    ),
                    subject: Translator::translate(
                        phraseId: 'field-has-invalid-value'
                    )
                )
            );
        }
    }
}
