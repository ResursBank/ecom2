<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
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
        #[StringLength(min: 2, max: 128)] public readonly string $displayName,
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]$/'
        )]
        public readonly string $termsUrl,
        #[StringMatchesRegex(
            pattern: '/^$|^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]$/'
        )]
        public readonly ?string $logoUrl = null,
        #[StringMatchesRegex(
            pattern: '/^$|^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]$/'
        )]
        public readonly ?string $homepageUrl = null
    ) {
        parent::__construct();
    }
}
