<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api\Auth;

/**
 * API authentication types.
 *
 * @codingStandardsIgnoreStart
 */
enum Types
{
    case Basic;
    case Jwt;
}
