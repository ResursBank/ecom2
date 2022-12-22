<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Lib\Cache\Redis;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Phrase;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Locale\Translator;

/**
 * Test that phrases can be translated.
 */
class TranslatorTest extends TestCase
{
    /**
     * @return void
     * @throws ConfigException
     */
    protected function setUp(): void
    {
        $this->setupConfig();
        Config::getCache()->clear(key: 'resursbank-ecom-translations');

        parent::setUp();
    }

    /**
     * @param Language $locale
     * @return void
     */
    private function setupConfig(Language $locale = Language::en): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            language: $locale,
            cache: new Redis(host: $_ENV['REDIS_HOST'])
        );
    }

    /**
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslationWorks(): void
    {
        $result = Translator::translate(phraseId: 'read-more');
        $this->assertSame(expected: 'Read More', actual: $result);

        // Test translating into swedish.
        $this->setupConfig(locale: Language::sv);
        $result = Translator::translate(phraseId: 'read-more');
        $this->assertSame(expected: 'Läs Mer', actual: $result);
    }

    /**
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslateThrowsWhenPhraseIdDoesNotExists(): void
    {
        $this->expectException(exception: TranslationException::class);
        Translator::translate(phraseId: 'read');
    }

    /**
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testDecodeDataThrowsIfDataIsFaulty(): void
    {
        $this->expectException(exception: JsonException::class);
        Translator::decodeData(data: 'not-there');
    }

    /**
     * @return void
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslateLoadsDataFromFile(): void
    {
        $cachedData = Config::getCache()->read(
            key: 'resursbank-ecom-translations'
        );

        $translatedData = Translator::translate(phraseId: 'read-more');

        $this->assertNull(actual: $cachedData);
        $this->assertNotEmpty(actual: $translatedData);
    }

    /**
     * @return void
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslateLoadsDataFromCache(): void
    {
        $phraseId = 'read-more';
        $oldCache = Config::getCache()->read(
            key: 'resursbank-ecom-translations'
        );
        $translatedString = Translator::translate(phraseId: $phraseId);
        $newCache = Config::getCache()->read(
            key: 'resursbank-ecom-translations'
        );

        $this->assertNotNull(actual: $newCache);

        $decodedCache = Translator::decodeData(data: $newCache);
        $result = null;

        /** @var Phrase $item */
        foreach ($decodedCache->toArray() as $item) {
            if ($item->id === $phraseId) {
                /** @var string $result */
                $result = $item->translation->{Config::getLanguage()->value};
            }
        }

        $this->assertNull(actual: $oldCache);
        $this->assertSame(expected: $translatedString, actual: $result);
    }
}
