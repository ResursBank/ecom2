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
use Resursbank\Ecom\Lib\Locale\Translation;

/**
 * Tests for the Resursbank\Ecom\Lib\Locale\Translation class.
 */
class TranslationTest extends TestCase
{
    /**
     * @return void
     */
    public function testValidateTranslationIsValidWhenNotEmpty(): void
    {
        $this->assertInstanceOf(
            expected: Translation::class,
            actual: new Translation(en: 'asdf', sv: 'asdf'),
        );
    }

    /**
     * @return void
     */
    public function testValidateIdThrowsIfSvEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        $this->assertInstanceOf(
            expected: Translation::class,
            actual: new Translation(en: 'asdf', sv: ''),
        );
    }

    /**
     * @return void
     */
    public function testValidateIdThrowsIfEnEmpty(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        $this->assertInstanceOf(
            expected: Translation::class,
            actual: new Translation(sv: 'asdf', en: ''),
        );
    }
}
