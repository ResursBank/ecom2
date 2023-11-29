<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCheckbox;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesUrl;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CreateCheckboxLinkDto object.
 */
class Link extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly ?string $text = null,
        #[StringMatchesUrl] public readonly ?string $url = null,
        public readonly ?string $body = null,
        public readonly ?bool $requiredReadAll = null
    ) {
        parent::__construct();
    }
}
