<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Shipping;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Currently selected shipping method information
 */
class Selection extends Model
{
    public function __construct(
        public readonly ?string $methodId = null,
        public readonly ?string $optionId = null,
        public readonly ?Type $type = null
    ) {
    }
}
