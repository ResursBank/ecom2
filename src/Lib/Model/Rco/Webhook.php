<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesUrl;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of WebhookDto object.
 */
class Webhook extends Model
{
    /**
     * @param string $url A https url to that will be posted to when [...]
     * @param string $authorization The Authorization header to set when doing the webhook.
     * @param bool|null $continueOnNoResponse Continue if no/unexpected response is returned from the webhook post.
     * @param int|null $timeout Timeout in seconds before giving up on a request.
     */
    public function __construct(
        #[StringNotEmpty] #[StringMatchesUrl] public readonly string $url,
        #[StringLength(
            min: 0,
            max: 16000
        )] public readonly string $authorization,
        public readonly ?bool $continueOnNoResponse,
        #[IntValue(
            min: 0,
            max: 180
        )] public readonly ?int $timeout
    ) {
        parent::__construct();
    }
}
