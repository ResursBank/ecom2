<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Options model for rco+ (not in pascal case). Model is lowerCased according to the docs.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Options extends Model
{
    /**
     * @param array $requiredFields @todo Can we make an enum array?
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    // phpcs:ignore
    public function __construct(
        public readonly bool $b2bEnabled = true,
        public readonly bool $renderCart = false,
        public readonly bool $mutableCart = false,
        public readonly bool $calculateShipping = false,
        public readonly bool $lookupB2CAddress = false,
        public readonly bool $renderCartCode = false,
        public readonly bool $renderNotes = false,
        public readonly array $requiredFields = ['EMAIL', 'PHONE', 'NAME', 'ADDRESS']
    ) {
    }
}
