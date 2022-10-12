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
    public function getFullyObfuscateTest()
    {
        $obfuscateFirst = Strings::getObfuscatedStringFull('Just a string.', 2);
        $obfuscateSecond = Strings::getObfuscatedStringFull('Just a string.', 3);
        $obfuscateThird = Strings::getObfuscatedStringFull('Just a string.', 4, 0);
        // Breaking rules.
        $obfuscateFourth = Strings::getObfuscatedStringFull('Just', 5, 5);
        static::assertTrue(
            $obfuscateFirst === 'Ju************.' &&
            $obfuscateSecond === 'Jus************.' &&
            $obfuscateThird === 'Just************' &&
            $obfuscateFourth === 'Just'
        );
    }
}
