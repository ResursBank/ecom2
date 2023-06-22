<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Webhooks;

/**
 * Webhook model for when the end user has made a payment selection.
 */
class Payment extends WebhookModel
{
    /**
     * @param string $url Posted to when the end user has made a payment selection.
     */
    public function __construct(string $url, string $authorization, bool $continueOnNoResponse, int $timeout)
    {
        parent::__construct(
            url: $url,
            authorization: $authorization,
            continueOnNoResponse: $continueOnNoResponse,
            timeout: $timeout
        );
    }
}
