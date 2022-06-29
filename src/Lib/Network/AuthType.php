<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

/**
 * API authentication types.
 *
 * @codingStandardsIgnoreStart
 */
enum AuthType
{
    case BASIC;
    case JWT;
    case NONE;
}
