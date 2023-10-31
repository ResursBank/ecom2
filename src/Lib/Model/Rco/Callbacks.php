<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CreateCallbacksDto object.
 */
class Callbacks extends Model
{
    public function __construct(
        public readonly ?Callback $authorization = null,
        public readonly ?Callback $management = null
    ) {
    }
}
