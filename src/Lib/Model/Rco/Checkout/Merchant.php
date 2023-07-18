<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Checkout;

use Resursbank\Ecom\Exception\UrlValidationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Lib\Validation\UrlValidation;

/**
 * Merchant model for RCO+.
 */
class Merchant extends Model
{
    /**
     * @param string $displayName Human readable display name.
     * @param ?string $logoUrl Https url pointing to a small logo. SVG is recommended.
     * @param ?string $homepageUrl Https fallback url pointing to the main page.
     * @throws EmptyValueException
     * @throws UrlValidationException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly string $displayName,
        public readonly ?string $logoUrl = null,
        public readonly ?string $homepageUrl = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateDisplayName();
        $this->validateProperUrls();
    }

    /**
     * @throws EmptyValueException
     */
    private function validateDisplayName(): void
    {
        $this->stringValidation->notEmpty(value: $this->displayName);
    }

    /**
     * Validate that data uses proper urls.
     *
     * @throws UrlValidationException
     */
    private function validateProperUrls(): void
    {
        // Since this data is not required, we allow them to be empty.
        if (!$this->logoUrl || !$this->homepageUrl) {
            return;
        }

        UrlValidation::validateMultipleUrls(
            urls: [$this->logoUrl, $this->homepageUrl]
        );
    }
}
