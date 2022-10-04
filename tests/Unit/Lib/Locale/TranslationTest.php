<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Locale;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Locale\Phrase;
use Resursbank\Ecom\Lib\Locale\Translation;

/**
 * Tests for the Resursbank\Ecom\Lib\Locale\Translation class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TranslationTest extends TestCase
{
    /**
     * @return void
     */
    public function testValidateTranslationIsValidWhenNotEmpty(): void
    {
        self::assertInstanceOf(
            expected: Translation::class,
            actual: new Translation(en: 'asdf', sv: 'asdf'),
        );
    }

    /**
     * @return void
     */
    public function testValidateIdThrowsIfAnyPropertyIsEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        self::assertInstanceOf(
            expected: Translation::class,
            actual: new Translation(en: 'asdf', sv: ''),
        );

        self::assertInstanceOf(
            expected: Translation::class,
            actual: new Translation(en: '', sv: 'asdf'),
        );
    }
}
