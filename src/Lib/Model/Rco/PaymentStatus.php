<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActions;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus as PaymentStatusEnum;

use function in_array;

/**
 * Implementation of PaymentStatusDto object.
 */
class PaymentStatus extends Model
{
    /**
     * @param array|null $availableActions This is actually an array of enum,
 * values see ECP-549, currently fixed using evaluateFields to convert data.
     */
    public function __construct(
        public readonly int $requestedAmount,
        public readonly int $authorizedAmount,
        public readonly int $cancelledAmount,
        public readonly int $capturedAmount,
        public readonly int $refundedAmount,
        public readonly ?PaymentStatusEnum $status = null,
        public ?array $availableActions = null
    ) {
        $this->evaluateAvailableActions();
    }

    /**
     * Convert anonymous strings to enum correspondent for availableActions.
     */
    private function evaluateAvailableActions(): void
    {
        if ($this->availableActions === null) {
            return;
        }

        $data = [];

        foreach ($this->availableActions as $field) {
            $data[] = in_array(
                needle: $field,
                haystack: AvailableActions::cases(),
                strict: true
            ) ? $field : AvailableActions::from(value: $field);
        }

        $this->availableActions = $data;
    }
}
