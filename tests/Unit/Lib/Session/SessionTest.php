<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Session;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\SessionException;
use Resursbank\Ecom\Exception\SessionValueException;
use Resursbank\Ecom\Lib\Session\Session;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Unit tests for Session.
 */
class SessionTest extends TestCase
{
    /**
     * Verify behavior of getKey.
     *
     * @throws Exception
     */
    public function testGetKey(): void
    {
        $baseKey = Strings::generateRandomString(length: 10);

        $this->assertEquals(
            expected: Session::PREFIX . $baseKey,
            actual: Session::getKey(key: $baseKey)
        );
    }

    /**
     * Verify basic behavior of get, set and delete.
     *
     * @throws SessionValueException
     * @throws SessionException
     */
    public function testGetSetDelete(): void
    {
        $baseKey = Strings::generateRandomString(length: 10);

        $session = new Session();

        $this->assertNull(
            actual: $session->get(key: $baseKey)
        );

        $session->set(key: $baseKey, val: 'foobar');

        $return = $session->get(key: $baseKey);

        $this->assertEquals(expected: 'foobar', actual: $return);

        $session->delete(key: $baseKey);

        $this->assertNull(
            actual: $session->get(key: $baseKey)
        );
    }

    /**
     * Verify get throws if stored value is non-string.
     *
     * @throws SessionException
     * @throws SessionValueException
     */
    public function testNonStringSessionValue(): void
    {
        $intValue = 234;
        $key = Strings::generateRandomString(length: 10);

        session_start();
        $_SESSION[Session::getKey(key: $key)] = $intValue;
        $session = new Session();

        $this->expectException(exception: SessionValueException::class);
        $session->get(key: $key);
    }
}
