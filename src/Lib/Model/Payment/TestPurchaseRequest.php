<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment;

use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

class TestPurchaseRequest extends Model
{
    public function __construct(
        public readonly Environment $environment,
        #[StringNotEmpty] public readonly string $clientId,
        #[StringNotEmpty] public readonly string $clientSecret
    ) {
    }
}
