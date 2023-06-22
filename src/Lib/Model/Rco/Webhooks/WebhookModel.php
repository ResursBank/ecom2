<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Webhooks;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Webhook Model Extender for RCO+.
 */
class WebhookModel extends Model
{
    /**
     * @param string $url A https url to that will be posted to when [...]
     * @param string $authorization The Authorization header to set when doing the webhook.
     * @param bool $continueOnNoResponse Continue if no/unexpected response is returned from the webhook post.
     * @param int $timeout Timeout in seconds before giving up on a request.
     */
    public function __construct(
        public readonly string $url,
        public readonly string $authorization,
        public readonly bool $continueOnNoResponse,
        public readonly int $timeout
    ) {
    }
}
