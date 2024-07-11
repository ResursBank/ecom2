<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Locale;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Locale\Translation;

/**
 * Tests for the Resursbank\Ecom\Lib\Locale\Translation class.
 */
class TranslationTest extends TestCase
{
    public function testValidateTranslationIsValidWhenNotEmpty(): void
    {
        $this->assertInstanceOf(
            expected: Translation::class,
            actual: new Translation(en: 'asdf')
        );
    }

    /**
     * Validate that empty translation strings default to the 'en' string.
     */
    public function testEmptyDefaultsToEnglish(): void
    {
        $translation = new Translation(
            en: 'foo',
            sv: '',
            fi: '',
            no: '',
            da: ''
        );

        foreach (['sv', 'fi', 'no', 'da'] as $locale) {
            $this->assertEquals(
                expected: $translation->en,
                actual: $translation->$locale
            );
        }

        $translation = new Translation(
            en: 'foo',
            sv: '   ',
            fi: '   ',
            no: '   ',
            da: '   '
        );

        foreach (['sv', 'fi', 'no', 'da'] as $locale) {
            $this->assertEquals(
                expected: $translation->en,
                actual: $translation->$locale
            );
        }
    }
}
