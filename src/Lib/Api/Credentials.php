<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

use Resursbank\Ecom\Exception\EmptyException;

/**
 * API credentials configuration object.
 */
class Credentials
{
    /**
     * @param string $username
     * @param string $password
     * @param bool $test
     * @throws EmptyException
     */
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly bool $test,
    ) {
        if ($this->username === '') {
            throw new EmptyException('Username');
        }

        if ($this->password === '') {
            throw new EmptyException('Password');
        }
    }
}
