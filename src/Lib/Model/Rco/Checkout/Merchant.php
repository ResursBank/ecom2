<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Merchant model for RCO+.
 */
class Merchant extends Model
{
    /**
     * @param string $displayName Human readable display name.
     * @param string $logoUrl Https url pointing to a small logo. SVG is recommended.
     * @param string $homepageUrl Https fallback url pointing to the main page.
     * @param string $accessControlAllowOrigin Https url of the page that will serve the web component.
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly string $displayName,
        public readonly string $logoUrl,
        public readonly string $homepageUrl,
        public readonly string $accessControlAllowOrigin
    ) {
    }
}
