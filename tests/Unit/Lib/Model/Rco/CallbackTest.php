<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Rco;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Model\Rco\Callback;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\EcomTest\Utilities\DataIntegrity;

/**
 * Integrity tests of \Resursbank\Ecom\Lib\Model\Rco\Callback
 */
class CallbackTest extends TestCase
{
    /**
     * Assert validation rules for url property.
     *
     * @throws Exception
     */
    public function testUrlValidation(): void
    {
        DataIntegrity::testValueIntegrity(
            accepted: [
                'https://somwhere.resurs.com/MyCoolPlace/whatever/whoever.html',
                'https://somwhere.resurs.com/something',
                'https://somwhere.resurs.com',
                'https://somwhere.resurs.com/hey-1/yes.php',
                'http://somwhere.resurs.com/?resource=wonky&result=bad'
            ],
            rejected: [
                'ftp://files.resurs.com',
                'htps://error.resurs.com',
                'http2://www.resurs.com',
                'http3://www.resurs.com',
                Strings::generateRandomString(length: 1),
                Strings::generateRandomString(length: 45),
                Strings::generateRandomString(length: 200)
            ],
            callback: static fn (string $v) => new Callback(url: $v),
            test: $this
        );
    }
}
