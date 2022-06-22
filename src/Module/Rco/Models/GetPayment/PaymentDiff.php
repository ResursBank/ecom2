<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models\GetPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines a GetPayment payment diff object
 */
class PaymentDiff extends Model
{
    public function __construct(
        public string $type,
        public ?string $transactionId,
        public string $created,
        public string $createdBy,
        public PaymentSpec $paymentSpec,
        public ?string $orderId,
        public ?string $invoiceId,
        public array $documentNames
    ) {
    }
}
