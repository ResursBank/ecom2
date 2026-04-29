<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Exception;
use JsonException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

use function chr;
use function ord;
use function strlen;

/**
 * Class for string manipulation.
 */
class Strings
{
    /**
     * Obfuscate strings between first and last character, just like RCO.
     */
    public static function getObfuscatedString(string $string, int $startAt = 1, int $endAt = 1): string
    {
        $stringLength = strlen(string: $string);
        return $stringLength > $startAt - 1 ?
            substr(string: $string, offset: 0, length: $startAt) .
            str_repeat(string: '*', times: $stringLength - 2) .
            substr(
                string: $string,
                offset: $stringLength - $endAt,
                length: $stringLength - $endAt
            ) : $string;
    }

    /**
     * Base64-encoded data, but with URL-safe characters.
     */
    public static function base64urlEncode(string $data): string
    {
        return rtrim(
            string: strtr(base64_encode(string: $data), '+/', '-_'),
            characters: '='
        );
    }

    /**
     * Generates a random string of characters.
     *
     * @param string|null $characters If set the string will only contain characters from this string.
     * @throws Exception
     */
    public static function generateRandomString(
        int $length,
        ?string $characters = null
    ): string {
        if (!$characters) {
            return substr(
                string: bin2hex(
                    string: random_bytes(length: max(1, $length))
                ),
                offset: 0,
                length: $length
            );
        }

        $generated = '';

        for ($i = 0; $i < $length; ++$i) {
            $generated .= count_chars(string: $characters, mode: 3)
                [rand(0, strlen($characters) - 2)];
        }

        return $generated;
    }

    /**
     * Base64-decoded data, but with URL-safe characters.
     */
    public static function base64urlDecode(string $data): string
    {
        return (string)base64_decode(
            string: str_pad(
                string: strtr($data, '-_', '+/'),
                length: strlen(string: $data) % 4,
                pad_string: '='
            ),
            strict: false
        );
    }

    /**
     * Generate a random UUID.
     *
     * @throws Exception
     * @throws IllegalValueException
     */
    public static function getUuid(): string
    {
        $data = random_bytes(length: 16);

        if (strlen(string: $data) !== 16) {
            throw new IllegalValueException(message: 'Missing random bytes.');
        }

        $data[6] = chr(codepoint: ord(character: $data[6]) & 0x0f | 0x40);
        $data[8] = chr(codepoint: ord(character: $data[8]) & 0x3f | 0x80);

        return vsprintf(
            format: '%s%s-%s-%s-%s-%s%s%s',
            values: str_split(string: bin2hex(string: $data), length: 4)
        );
    }

    /**
     * Check if supplied string is a UUID.
     *
     * @param string $value Value to check
     * @return bool True if input value is a UUID string.
     */
    public static function isUuid(string $value): bool
    {
        return (bool) preg_match(
            pattern: '/^[\da-f]{8}-[\da-f]{4}-[0-5][\da-f]{3}-[\da-d][\da-f]{3}-[\da-f]{12}$/i',
            subject: $value
        );
    }

    /**
     * Check if supplied string is a Swedish SSN.
     */
    public static function isSwedishSsn(string $value): bool
    {
        return (bool) preg_match(
            pattern: '/^(18\d{2}|19\d{2}|20\d{2}|\d{2})' .
            '(0[1-9]|1[0-2])' .
            '(0[1-9]|[1-2][0-9]|3[0-1])' .
            '([-+])?(\d{4})$/',
            subject: $value
        );
    }

    /**
     * Check if supplied string is a Swedish org number.
     */
    public static function isSwedishOrgNo(string $value): bool
    {
        return (bool) preg_match(
            pattern: '/^(16\d{2}|18\d{2}|19\d{2}|20\d{2}|\d{2})' .
            '(\d{2})(\d{2})([-+])?(\d{4})$/',
            subject: $value
        );
    }

    /**
     * Check if supplied string is a URL.
     */
    public static function isUrl(string $value): bool
    {
        if (!filter_var(value: $value, filter: FILTER_VALIDATE_URL)) {
            return false;
        }

        return true;
    }

    /**
     * Verify that string is not empty.
     *
     * This method mirrors now deleted method StringValidation::noEmpty.
     */
    public static function notEmpty(string $value): bool
    {
        return trim(string: $value) !== '';
    }

    /**
     * Verify that string is JSON.
     */
    public static function isJson(string $value): bool
    {
        try {
            json_decode(
                json: $value,
                associative: false,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            if (json_last_error() === JSON_ERROR_NONE) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verify that string is timestamp/date.
     *
     * This method mirrors now deleted method StringValidation::isTimestampDate.
     */
    public static function isTimestampDate(string $value): bool
    {
        return strtotime(datetime: $value) !== false;
    }

    /**
     * Verify that string could be an email address.
     */
    public static function isEmail(string $value): bool
    {
        return str_contains(haystack: $value, needle: '@');
    }
}
