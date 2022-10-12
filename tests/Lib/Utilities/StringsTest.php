<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Utilities;

use Resursbank\Ecom\Lib\Utilities\Strings;
use PHPUnit\Framework\TestCase;

/**
 * String testing.
 */
class StringsTest extends TestCase
{
    /**
     * @test
     */
    public function testGetObfuscatedString()
    {
        $obfuscateFromSecondPosition = Strings::getObfuscatedString(string: 'Just a string.', startAt: 2);
        $obfuscateFromThirdPosition = Strings::getObfuscatedString(string: 'Just a string.', startAt: 3);
        $obfuscateFromFourthPositionEndAtZero = Strings::getObfuscatedString(string: 'Just a string.', startAt: 4, endAt: 0);
        // Breaking rules.
        $obfuscateFromFifthAndBreakTheStrLenRules = Strings::getObfuscatedString(string: 'Just', startAt: 5, endAt: 5);

        self::assertEquals(expected: 'Ju************.', actual: $obfuscateFromSecondPosition);
        self::assertEquals(expected: 'Jus************.', actual: $obfuscateFromThirdPosition);
        self::assertEquals(expected: 'Just************', actual: $obfuscateFromFourthPositionEndAtZero);
        self::assertEquals(expected: 'Just', actual: $obfuscateFromFifthAndBreakTheStrLenRules);
    }
}
