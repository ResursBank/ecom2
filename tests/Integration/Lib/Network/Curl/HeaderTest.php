<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network\Curl;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Network\Curl\Header;

/**
 * Tests for Header.
 */
class HeaderTest extends TestCase
{
    /**
     * Verify that exception is thrown for invalid header array data.
     *
     * @throws ConfigException
     */
    public function testThrowsOnInvalidHeaderArray(): void
    {
        Config::setup();

        $this->expectException(exception: InvalidArgumentException::class);
        Header::getHeadersData(
            headers: [
                'foo'
            ]
        );
    }
}
