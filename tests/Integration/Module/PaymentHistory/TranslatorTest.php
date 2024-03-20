<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentHistory;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Module\PaymentHistory\Translator;

/**
 * Tests module specific translator class.
 */
class TranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        Config::setup();
        parent::setUp();
    }

    /**
     * Assert translations are read from the module's local translation file.
     *
     * @throws TranslationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ConfigException
     * @throws ReflectionException
     * @throws FilesystemException
     */
    public function testTranslate(): void
    {
        $this->assertSame(
            expected: 'Payment fully captured.',
            actual: Translator::translate(phraseId: 'event-captured')
        );

        $this->assertSame(
            expected: 'Payment fully canceled.',
            actual: Translator::translate(phraseId: 'event-canceled')
        );
    }
}
