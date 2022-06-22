<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api\Auth;

/**
 * Defines basic API authentication.
 */
class Basic
{
    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }
}