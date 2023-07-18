<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Webhooks;

use Resursbank\Ecom\Lib\Model\Rco\Webhooks;

/**
 * Webhook model for when the customer fields have been changed.
 */
class Customer extends Webhooks
{
    /**
     * @param string $url Posted to when the customer fields have been changed.
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
