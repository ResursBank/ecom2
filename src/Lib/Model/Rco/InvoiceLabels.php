<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Transaction collection
 */
class InvoiceLabels extends Model
{
    public function __construct(
        #[StringLength(min: 0, max: 20)] public readonly ?string $customerId = null,
        #[StringLength(min: 0, max: 20)] public readonly ?string $yourReference = null,
    ) {
        parent::__construct();
    }
}
