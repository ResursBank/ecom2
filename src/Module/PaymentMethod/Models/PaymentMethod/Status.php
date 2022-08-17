<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines the status property of a payment method and associated values.
 */
class Status extends Model
{
    /**
     * @param bool $disabled
     * @param bool $requireLimitRaise
     * @param array $disabledReasons
     */
    public function __construct(
        public readonly bool $disabled,
        public readonly bool $requireLimitRaise,
        public readonly array $disabledReasons,
    ) {
    }
}
