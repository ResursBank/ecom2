<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;

use function file_get_contents;
use function json_decode;
use function is_string;

/**
 * Methods to extract locale specific phrases. The intention is to maintain
 * consistent terminology between implementations.
 */
class Translator
{
    /**
     * Path to the translations file that holds all translations in Ecom.
     *
     * @var string
     */
    private static string $translationsFilePath = __DIR__ . '/Resources/phrases.json';

    /**
     * Key to store cached translations under.
     *
     * @var string
     */
    private static string $cacheKey = 'resursbank-ecom-translations';

    /**
     * Prevent object instantiation
     */
    private function __construct()
    {
    }

    /**
     * Loads translations file from disk, decodes the result into a collection
     * and returns that collection, and caches the resulting collection.
     *
     * @return PhraseCollection
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    private static function load(): PhraseCollection
    {
        $file = file_get_contents(filename: self::$translationsFilePath);

        if (!is_string($file)) {
            throw new FilesystemException(
                message: 'Translations file could not be found on path: ' .
                    self::$translationsFilePath
            );
        }

        $result = self::decodeData(data: $file);

        Config::$instance->cache->write(
            key: self::$cacheKey,
            data: json_encode(
                value: $result->toArray(),
                flags: JSON_THROW_ON_ERROR
            ),
            ttl: 3600
        );

        return $result;
    }

    /**
     * Takes an english phrase and translates it to the language of the
     * configured locale.
     *
     * @param string $phrase
     * @return string
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalValueException
     * @see Config::locale
     */
    public static function translate(string $phrase): string
    {
        $cachedData = Config::$instance->cache->read(key: self::$cacheKey);
        $result = '';

        if ($cachedData === null) {
            $phrases = self::load();
        } else {
            $phrases = self::decodeData(data: $cachedData);
        }

        /** @var Phrase $item */
        foreach ($phrases as $item) {
            if ($item->en === $phrase) {
                /** @var string $result */
                $result = $item->{Config::$instance->locale->value};
            }
        }

        if ($result === '') {
            throw new IllegalValueException(
                message: "A translation for \"${phrase}\" could not be found."
            );
        }

        return $result;
    }

    /**
     * Decodes JSON data into a collection of phrases.
     *
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ReflectionException
     */
    private static function decodeData(string $data): PhraseCollection
    {
        /** @var array $decode */
        $decode = json_decode(
            json: $data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        /** @var PhraseCollection $result */
        $result = DataConverter::arrayToCollection(
            data: $decode,
            targetType: Phrase::class,
        );

        return $result;
    }
}
