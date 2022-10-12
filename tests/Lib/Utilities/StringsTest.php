<?php

namespace Resursbank\EcomTest\Integration\Lib\Utilities;

use Resursbank\Ecom\Lib\Utilities\Strings;
use PHPUnit\Framework\TestCase;

class StringsTest extends TestCase
{
    /**
     * @test
     */
    public function testGetObfuscatedString()
    {
        $obfuscateFirst = Strings::getObfuscatedString(string: 'Just a string.', startAt: 2);
        $obfuscateSecond = Strings::getObfuscatedString(string: 'Just a string.', startAt: 3);
        $obfuscateThird = Strings::getObfuscatedString(string: 'Just a string.', startAt: 4, endAt: 0);
        // Breaking rules.
        $obfuscateFourth = Strings::getObfuscatedString(string: 'Just', startAt: 5, endAt: 5);

        self::assertEquals(expected: 'Ju************.', actual: $obfuscateFirst);
        self::assertEquals(expected: 'Jus************.', actual: $obfuscateSecond);
        self::assertEquals(expected: 'Just************', actual: $obfuscateThird);
        self::assertEquals(expected: 'Just', actual: $obfuscateFourth);
    }
}
