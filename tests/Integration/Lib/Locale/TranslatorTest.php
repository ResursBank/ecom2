<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Locale;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Locale\Translator;

/**
 * Test that phrases can be translated.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslatorTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            locale: Locale::sv
        );

        parent::setUp();
    }

    /**
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws IllegalValueException
     */
    public function testTranslationWorks(): void
    {
        $result = Translator::translate('Read More');

        self::assertSame(expected: 'Läs Mer', actual: $result);
    }

    /**
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     */
    public function testTranslateThrowsWhenPhraseDoesNotExists(): void
    {
        $this->expectException(IllegalValueException::class);
        Translator::translate(phrase: 'Read');
    }
}
