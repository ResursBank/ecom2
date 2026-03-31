<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network;

use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Model;
use stdClass;

/**
 * Curl response.
 */
class Response extends Model
{
    public function __construct(
        public readonly stdClass $body,
        #[IntValue(min:100, max:599)] public readonly int $code
    ) {
        parent::__construct();
    }
}
