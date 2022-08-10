<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;

/**
 * Tests Config class functionality
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * 
 * @todo Improve test coverage.
 */
class ConfigTest extends TestCase
{
    /**
     * Verifies that the hasBasicAuth method behaves as expected
     *
     * @return void
     */
    public function testHasBasicAuth(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $this->assertEquals(
            expected: false,
            actual: Config::hasBasicAuth()
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: $this->createMock(originalClassName: Basic::class)
        );
        $this->assertEquals(
            expected: true,
            actual: Config::hasBasicAuth()
        );
    }

    /**
     * Verifies that the hasJwtAuth method behaves as expected
     *
     * @return void
     */
    public function testHasJwtAuth(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class)
        );
        $this->assertEquals(
            expected: false,
            actual: Config::hasJwtAuth()
        );

        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            jwtAuth: $this->createMock(originalClassName: Jwt::class)
        );
        $this->assertEquals(
            expected: true,
            actual: Config::hasJwtAuth()
        );
    }
}
