<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;

/**
 * Implementation of OptionsDto object.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Options extends Model
{
    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly ?bool $b2bEnabled = null,
        public readonly ?bool $renderCart = null,
        public readonly ?bool $calculateShipping = null,
        public readonly ?bool $lookupB2CAddress = null,
        public readonly ?bool $renderCartCode = null,
        public readonly ?bool $renderNotes = null,
        public readonly ?RequiredCollection $requiredFields = null
    ) {
    }
}
