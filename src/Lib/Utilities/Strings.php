<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

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
            string: strtr(base64_encode($data), '+/', '-_'),
            characters: '='
        );
    }

    /**
     * Base64-decoded data, but with URL-safe characters.
     */
    public static function base64urlDecode(string $data): string
    {
        return (string)base64_decode(
            string: str_pad(
                string: strtr($data, '-_', '+/'),
                length: strlen($data) % 4,
                pad_string: '='
            ),
            strict: false
        );
    }
}
