<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of UpdateOrderReferenceDto
 */
class UpdateOrderReference extends Model
{
    public function __construct(
        #[StringMatchesRegex(pattern: '/^$|^[a-zA-Z0-9]{1,32}$/')]
        public readonly string $orderReference
    ) {
        parent::__construct();
    }
}
