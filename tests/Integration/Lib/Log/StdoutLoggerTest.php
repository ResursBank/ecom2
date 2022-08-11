<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Log;

use PHPUnit\Framework\TestCase;

/**
 * Verifies that the
 * @psalm-suppress PropertyNotSetInConstructor
 * @todo Completed test coverage.
 */
class StdoutLoggerTest extends TestCase
{
    public function testLogDebug(): void
    {
        $this->markTestSkipped(message: 'Marking skipped as I have yet to find a way of capturing STDOUT/STDERR '
            . 'output reliably');
    }
}
