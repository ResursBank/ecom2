<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Widget\Checkout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines the CSS variable values used by the RCO widget.
 */
class Style extends Model
{
    public function __construct(
        public readonly ?string $primaryColor = null,
        public readonly ?string $buttonBgColor = null,
        public readonly ?string $buttonFontColor = null,
        public readonly ?string $iconColor = null,
        public readonly ?string $linkColor = null,
        public readonly ?string $margin = null,
        public readonly ?string $padding = null,
        public readonly ?string $buttonRadius = null
    ) {
    }
}
