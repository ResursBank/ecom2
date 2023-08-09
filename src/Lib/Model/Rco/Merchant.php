<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of MerchantDto object.
 */
class Merchant extends Model
{
    /**
     * @param string $displayName Human readable display name.
     * @param ?string $logoUrl Https url pointing to a small logo. SVG is recommended.
     * @param ?string $homepageUrl Https fallback url pointing to the main page.
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly string $displayName,
        public readonly ?string $logoUrl = null,
        public readonly ?string $homepageUrl = null
    ) {
    }
}
