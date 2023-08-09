<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkbox;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of CheckboxLinkDto object.
 */
class Link extends Model
{
    public function __construct(
        public readonly ?string $text = null,
        public readonly ?string $url = null,
        public readonly ?string $body = null,
        public readonly ?bool $requiredReadAll = null
    ) {
    }
}
