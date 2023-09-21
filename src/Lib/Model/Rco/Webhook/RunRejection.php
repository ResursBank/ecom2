<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Webhook;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of WebhookRunRejectionDto object.
 */
class RunRejection extends Model
{
    public function __construct(
        public readonly ?string $message
    ) {
        parent::__construct();
    }
}
