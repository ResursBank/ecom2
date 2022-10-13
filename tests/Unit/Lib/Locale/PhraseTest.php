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
 * Tests for the Resursbank\Ecom\Lib\Locale\Phrase class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PhraseTest extends TestCase
{
    /**
     * @return Translation
     */
    private function getTranslationInstance(): Translation
    {
        return new Translation(en: 'asdf', sv: 'asdf');
    }

    /**
     * @return void
     * @throws EmptyValueException
     */
    public function testValidateIdIsValidWhenNotEmpty(): void
    {
        self::assertInstanceOf(
            expected: Phrase::class,
            actual: new Phrase(
                id: 'asdf',
                translation: $this->getTranslationInstance(),
            ),
        );
    }

    /**
     * @return void
     * @throws EmptyValueException
     */
    public function testValidateIdThrowsIfEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        new Phrase(
            id: '',
            translation: $this->getTranslationInstance(),
        );
    }
}
