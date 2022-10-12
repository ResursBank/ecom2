<?php

namespace Resursbank\Ecom\Lib\Utilities;

class Strings
{
    /**
     * Just obfuscate strings between first and last character.
     *
     * @param string $string
     * @param int $startAt
     * @param int $endAt
     * @return string
     */
    public static function getObfuscatedString(string $string, int $startAt = 1, int $endAt = 1): string
    {
        $stringLength = strlen($string);
        return $stringLength > $startAt - 1 ?
            substr($string, 0, $startAt) . str_repeat('*', $stringLength - 2) . substr(
                $string,
                $stringLength - $endAt,
                $stringLength - $endAt
            ) : $string;
    }
}