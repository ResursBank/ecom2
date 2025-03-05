<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;

use function file_get_contents;
use function is_string;
use function json_decode;

/**
 * Methods to extract language-specific phrases. The intention is to maintain
 * consistent terminology between implementations.
 *
 * @todo Check if ConfigException require test.
 */
abstract class Translator
{
    /**
     * Path to the translations file that holds all translations in Ecom.
     */
    private static string $translationsFilePath = __DIR__ . '/Resources/translations.json';

    /**
     * Key to store cached translations under.
     */
    private static string $cacheKey = 'resursbank-ecom-translations';

    /**
     * Loads translations file from disk, decodes the result into an array
     * and returns that array, and caches the resulting array.
     *
     * @throws FilesystemException
     * @throws JsonException
     * @throws ConfigException
     */
    public static function load(?string $translationFile = null): array
    {
        $translationFilePath = $translationFile ?? self::$translationsFilePath;

        if (!file_exists(filename: $translationFilePath)) {
            throw new FilesystemException(
                message: 'Translations file could not be found on path: ' .
                    self::$translationsFilePath,
                code: FilesystemException::CODE_FILE_MISSING
            );
        }

        $content = file_get_contents(filename: $translationFilePath);

        if (!is_string(value: $content) || $content === '') {
            throw new FilesystemException(
                message: 'Translation file ' . $translationFilePath .
                    ' is empty.',
                code: FilesystemException::CODE_FILE_EMPTY
            );
        }

        $result = self::decodeData(data: $content);

        Config::getCache()->write(
            key: self::getCacheKey(translationFile: $translationFile),
            data: json_encode(
                value: $result,
                flags: JSON_THROW_ON_ERROR
            ),
            ttl: 3600
        );

        return $result;
    }

    /**
     * Takes an english phrase and translates it to the configured language.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws JsonException
     * @throws TranslationException
     * @see Config::$language
     */
    public static function translate(
        string $phraseId,
        ?string $translationFile = null
    ): string {
        $phrases = self::getData(translationFile: $translationFile);
        $result = self::findMatchingPhrase(
            phrases: $phrases,
            phraseId: $phraseId
        );

        if ($result === null) {
            throw new TranslationException(
                message: "A translation with $phraseId could not be found."
            );
        }

        return $result;
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     * @throws ConfigException
     */
    public static function getData(?string $translationFile = null): array
    {
        $cachedData = Config::getCache()->read(
            key: self::getCacheKey(translationFile: $translationFile)
        );

        return $cachedData === null
            ? self::load(translationFile: $translationFile)
            : self::decodeData(data: $cachedData);
    }

    /**
     * Decodes JSON data into an array of phrases.
     *
     * @throws JsonException
     */
    public static function decodeData(string $data): array
    {
        /** @var array $decode */
        $decode = json_decode(
            json: $data,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        return $decode;
    }

    /**
     * Generates a valid cache key which includes the name of the translation file.
     *
     * @todo preg_replace returns null|array|string, this method is required to return string ECP-372
     */
    public static function getCacheKey(?string $translationFile = null): string
    {
        $rawKey = ($translationFile ?
            (self::$cacheKey . '-' . $translationFile) :
            (self::$cacheKey . '-' . preg_replace(
                pattern: '/[0-9]/',
                replacement: '',
                subject: sha1(string: self::$translationsFilePath)
            ))
        );
        $key = preg_replace(
            pattern: '/[^a-zA-Z\d\-_]/',
            replacement: '-',
            subject: $rawKey
        );

        if (is_string(value: $key)) {
            return $key;
        }

        return '';
    }

    /**
     * Attempt to find a matching phrase in an array of phrases.
     *
     * @return string|null Found translation or null if no translation found.
     * @throws ConfigException
     */
    private static function findMatchingPhrase(
        array $phrases,
        string $phraseId
    ): ?string {
        foreach ($phrases as $item) {
            if ($item['id'] !== $phraseId) {
                continue;
            }

            if (
                array_key_exists(
                    Config::getLanguage()->value,
                    $item['translation']
                )
            ) {
                return $item['translation'][Config::getLanguage()->value];
            }

            return $item['translation']['en'];
        }

        return null;
    }
}
