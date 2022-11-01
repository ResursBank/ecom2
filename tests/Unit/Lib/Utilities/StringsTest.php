<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Utilities;

use Resursbank\Ecom\Lib\Utilities\Strings;
use PHPUnit\Framework\TestCase;

/**
 * String testing.
 */
class StringsTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetObfuscatedString(): void
    {
        $from2 = Strings::getObfuscatedString(string: 'Just a string.', startAt: 2);
        $from3 = Strings::getObfuscatedString(string: 'Just a string.', startAt: 3);
        $from4 = Strings::getObfuscatedString(string: 'Just a string.', startAt: 4, endAt: 0);
        // Breaking rules.
        $substrAt5 = Strings::getObfuscatedString(string: 'Just', startAt: 5, endAt: 5);

        self::assertEquals(expected: 'Ju************.', actual: $from2);
        self::assertEquals(expected: 'Jus************.', actual: $from3);
        self::assertEquals(expected: 'Just************', actual: $from4);
        self::assertEquals(expected: 'Just', actual: $substrAt5);
    }
}
