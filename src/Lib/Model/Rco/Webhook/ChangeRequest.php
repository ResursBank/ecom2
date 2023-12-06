<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Webhook;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateRecipient;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethodCollection;

/**
 * Implementation of WebhookChangeRequestDto object.
 */
class ChangeRequest extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly ?CreateRecipient $delivery,
        public readonly ?CreateRecipient $billing,
        public readonly ?CreateShippingMethodCollection $shippingMethods,
        #[StringLength(min: 0, max: 128)] public readonly ?string $cartCode,
        #[StringMatchesRegex(
            pattern: '/^$|^[a-zA-Z0-9]{1,32}$/'
        )] public readonly ?string $orderReference,
        #[CollectionSize(
            min: 1,
            max: 256
        )] public readonly ?ItemCollection $cartItems
    ) {
        parent::__construct();
    }
}
