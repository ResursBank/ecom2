<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * CoApplicant for Customer models in MAPI.
 */
class CoApplicant extends Model
{
    public function __construct(
        public readonly string $governmentId,
        public readonly string $mobilePhone,
        public readonly string $phone,
        public readonly string $email,
        public readonly ?Identification $identification = null
    ) {
    }
}
