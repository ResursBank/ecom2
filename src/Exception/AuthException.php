<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Exception;

use Exception;
use Throwable;

/**
 * Authentication exceptions.
 */
class AuthException extends Exception
{
    public function __construct(string $message = "", int|string $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
