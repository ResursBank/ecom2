<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesUrl;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CreateWebhookDto object.
 */
class CreateWebhook extends Model
{
    /**
     * @param string $url A https url to that will be posted to when [...]
     * @param string|null $authorization The Authorization header to set when doing the webhook.
     * @param bool|null $continueOnNoResponse Continue if no/unexpected response is returned from the webhook post.
     * @param int|null $timeout Timeout in seconds before giving up on a request.
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    public function __construct(
        #[StringNotEmpty] #[StringMatchesUrl] public readonly string $url,
        #[StringLength(
            min: 0,
            max: 16000
        )] public readonly ?string $authorization = null,
        public readonly ?bool $continueOnNoResponse = null,
        #[IntValue(
            min: 0,
            max: 180
        )] public readonly ?int $timeout = null
    ) {
        parent::__construct();
    }
}
