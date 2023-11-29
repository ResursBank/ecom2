<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkbox;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CheckboxLinkDto object.
 */
class Link extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly string $text,
        public readonly string $url,
        public readonly string $body,
        public readonly bool $requiredReadAll
    ) {
        parent::__construct();
    }
}
