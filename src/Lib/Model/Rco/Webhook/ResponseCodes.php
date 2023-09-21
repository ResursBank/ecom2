<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Webhook;

/**
 * List of possible status codes when responding to a webhook request.
 */
enum ResponseCodes: int
{
    case ACCEPTED = 200;
    case REJECTED = 422;
}
