<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Locale;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Phrase;
use Resursbank\Ecom\Lib\Locale\Translation;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Tests for the Resursbank\Ecom\Lib\Locale\Phrase class.
 */
class PhraseTest extends TestCase
{
    private function getTranslationInstance(): Translation
    {
        return new Translation(en: 'asdf', sv: 'asdf');
    }

    /**
     * Assert that valid IDs don't throw exceptions.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     */
    public function testValidIds(): void
    {
        for ($i = 0; $i < 10; $i++) {
            new Phrase(
                id: Strings::getUuid(),
                translation: $this->getTranslationInstance()
            );
            $this->addToAssertionCount(count: 1);
        }
    }

    /**
     * Assert that an exception is thrown when using an invalid ID value.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testInvalidIds(): void
    {
        for ($i = 0; $i < 10; $i++) {
            try {
                new Phrase(
                    id: Strings::generateRandomString(
                        length: 24,
                        characters: 'ABCDEFGHIJKLMNOPQRSTUV_'
                    ),
                    translation: $this->getTranslationInstance()
                );
            } catch (IllegalValueException) {
                $this->addToAssertionCount(count: 1);
            }
        }
    }
}
