<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentMethod\Models;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Lib\Cache\Redis;
use Resursbank\Ecom\Lib\Locale\Locale;
use Resursbank\Ecom\Lib\Locale\Phrase;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Locale\Translator;

/**
 * Test that phrases can be translated.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class TranslatorTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->setupConfig();
        Config::$instance->cache->clear(key: 'resursbank-ecom-translations');

        parent::setUp();
    }

    /**
     * @param Locale $locale
     * @return void
     */
    private function setupConfig(Locale $locale = Locale::en): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            locale: $locale,
            cache: new Redis(host: (string) $_ENV['REDIS_HOST'])
        );
    }

    /**
     * @return void
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     */
    public function testTranslationWorks(): void
    {
        $result = Translator::translate(phraseId: 'read-more');
        $this->assertSame(expected: 'Read More', actual: $result);

        // Test translating into swedish.
        $this->setupConfig(Locale::sv);
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
        Translator::decodeData(data: 'asdfsda');
    }

    /**
     * @return void
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testTranslateLoadsDataFromFile(): void
    {
        $cachedData = Config::$instance->cache->read(
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
     */
    public function testTranslateLoadsDataFromCache(): void
    {
        $phraseId = 'read-more';
        $oldCache = Config::$instance->cache->read(
            key: 'resursbank-ecom-translations'
        );
        $translatedString = Translator::translate(phraseId: $phraseId);
        $newCache = Config::$instance->cache->read(
            key: 'resursbank-ecom-translations'
        );

        $this->assertNotNull(actual: $newCache);

        $decodedCache = Translator::decodeData(data: $newCache);
        $result = null;

        /** @var Phrase $item */
        foreach ($decodedCache->toArray() as $item) {
            if ($item->id === $phraseId) {
                /** @var string $result */
                $result = $item->translation->{Config::$instance->locale->value};
            }
        }

        $this->assertNull(actual: $oldCache);
        $this->assertSame(expected: $translatedString, actual: $result);
    }
}
