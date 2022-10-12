<?php

namespace Resursbank\EcomTest\Integration\Lib\Utilities;

use Resursbank\Ecom\Lib\Utilities\Strings;
use PHPUnit\Framework\TestCase;

class StringsTest extends TestCase
{
    /**
     * @test
     * @since 6.1.6
     */
    public function getObfuscatedStringTest()
    {
        $obfuscateFirst = Strings::getObfuscatedString('Just a string.', 2);
        $obfuscateSecond = Strings::getObfuscatedString('Just a string.', 3);
        $obfuscateThird = Strings::getObfuscatedString('Just a string.', 4, 0);
        // Breaking rules.
        $obfuscateFourth = Strings::getObfuscatedString('Just', 5, 5);

        self::assertEquals('Ju************.', $obfuscateFirst);
        self::assertEquals('Jus************.', $obfuscateSecond);
        self::assertEquals('Just************', $obfuscateThird);
        self::assertEquals('Just', $obfuscateFourth);
    }
}
