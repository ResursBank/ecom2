<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

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
        public readonly ?bool $b2bEnabled = null,
        public readonly ?bool $renderCart = null,
        public readonly ?bool $mutableCart = null,
        public readonly ?bool $calculateShipping = null,
        public readonly ?bool $lookupB2CAddress = null,
        public readonly ?bool $renderCartCode = null,
        public readonly ?bool $renderNotes = null,
        public readonly array $requiredFields = ['EMAIL', 'PHONE', 'NAME', 'ADDRESS']
    ) {
    }
}
