<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Simplified;

/**
 * Configuration directives affecting Simplified Flow methods.
 */
class Config
{
    /**
     * @param bool $waitForFraudControl
     * @param bool $annulIfFrozen
     * @param bool $finalizeIfBooked
     */
    public function __construct(
        public readonly bool $waitForFraudControl = false,
        public readonly bool $annulIfFrozen = false,
        public readonly bool $finalizeIfBooked = false,
    ) {
    }
}
