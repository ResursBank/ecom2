<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

/**
 * URLs to merchant portal test/prod.
 *
 * @codingStandardsIgnoreStart
 */
enum MerchantPortal: string
{
    case TEST = 'https://web-integration-mock-merchant-portal.test.resurs.loc/login';
    case PROD = 'https://merchantportal.resurs.com/login';
}
