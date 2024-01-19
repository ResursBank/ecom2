<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\PaymentHistory;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Module\PaymentHistory\Repository;

/**
 * Test data integrity of PaymentHistory Repository methods.
 */
class RepositoryTest extends TestCase
{
    /**
     * Assert the getError method converts Throwable.
     */
    public function testGetError(): void
    {
        $error = new TestException(message: 'A simple test.');

        $data = Repository::getError(error: $error);

        $this->assertStringContainsString(
            needle: $error->getMessage(),
            haystack: $data
        );
        $this->assertStringContainsString(
            needle: $error->getFile(),
            haystack: $data
        );
        $this->assertStringContainsString(
            needle: $error->getTraceAsString(),
            haystack: $data
        );
        $this->assertStringContainsString(
            needle: ':: ' . $error->getLine(),
            haystack: $data
        );
    }
}
