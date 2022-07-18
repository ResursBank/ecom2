<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Log;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Log\StdoutLogger;

/**
 * Verifies that the
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StdoutLoggerTest extends TestCase
{
    public function testLogDebug(): void
    {
        $this->markTestSkipped(message: 'Marking skipped as I have yet to find a way of capturing STDOUT/STDERR '
            . 'output reliably');
    }
}
