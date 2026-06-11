<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Exception;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Attribute\Validation\Traits\TranslatifyPropertyName;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Tests for ValidationException.
 */
class ValidationExceptionTest extends TestCase
{
    use TranslatifyPropertyName;

    protected function setUp(): void
    {
        Config::setup();
        parent::setUp();
    }

    /**
     * Verify basic behavior of getFriendlyMessage method.
     *
     * @throws JsonException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     */
    public function testGetFriendlyMessage(): void
    {
        // Should work
        $propertyName = 'enableCapture';
        $errorId = 'limit-new-value-above-max';

        $this->assertEquals(
            expected: str_replace(
                search: '%1',
                replace: Translator::translate(
                    phraseId: self::convert(propertyName: $propertyName)
                ),
                subject: Translator::translate(
                    phraseId: $errorId
                )
            ),
            actual: ValidationException::getFriendlyMessage(
                propertyName: $propertyName,
                errorId: $errorId
            )
        );

        // Should return null
        $propertyName = Strings::generateRandomString(length: 12);
        $errorId = Strings::generateRandomString(length: 12);

        $this->assertNull(
            actual: ValidationException::getFriendlyMessage(
                propertyName: $propertyName,
                errorId: $errorId
            )
        );
    }
}
