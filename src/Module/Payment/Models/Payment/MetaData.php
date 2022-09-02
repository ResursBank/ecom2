<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * MetaData information class for payments. Currently, it does not have a proper collection.
 */
class MetaData extends Model
{
    public function __construct(
        public readonly ?string $creator = null,
        public readonly ?array $custom = null,
    ) {
    }
}
