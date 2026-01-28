<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\Network\Auth\Rws;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Model\Network\Auth\Rws\SessionToken;

/**
 * Tests for RWS session token.
 */
class SessionTokenTest extends TestCase
{
    /**
     * Assert isExpired returns true when expiresAt is in the past.
     */
    public function testIsExpiredReturnsTrueWhenExpired(): void
    {
        $a = 'asd';
        $token = new SessionToken(
            token: 'test-token',
            expiresAt: date('Y-m-d H:i:s', strtotime('-1 hour'))
        );

        self::assertTrue(condition: $token->isExpired());
    }

    /**
     * Assert isExpired returns false when expiresAt is in the future.
     */
    public function testIsExpiredReturnsFalseWhenNotExpired(): void
    {
        $token = new SessionToken(
            token: 'test-token',
            expiresAt: date('Y-m-d H:i:s', strtotime('+1 hour'))
        );

        self::assertFalse(condition: $token->isExpired());
    }

    /**
     * Assert isExpired returns true when expiresAt is exactly now.
     */
    public function testIsExpiredReturnsTrueWhenExpiresAtIsNow(): void
    {
        $token = new SessionToken(
            token: 'test-token',
            expiresAt: date('Y-m-d H:i:s')
        );

        self::assertTrue(condition: $token->isExpired());
    }
}
