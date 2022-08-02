<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Model\Item;

/**
 * Defines the status property of a payment method and associated values.
 */
class Status
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
