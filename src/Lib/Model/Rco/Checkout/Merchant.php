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
     * @param string $logoUrl Https url pointing to a small logo. SVG is recommended.
     * @param string $homepageUrl Https fallback url pointing to the main page.
     * @param string $accessControlAllowOrigin Https url of the page that will serve the web component.
     * @throws EmptyValueException
     * @throws UrlValidationException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly string $displayName,
        public readonly string $logoUrl,
        public readonly string $homepageUrl,
        public readonly string $accessControlAllowOrigin,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateDisplayName();
        $this->validateAccessControlAllowOrigin();
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
     * @throws EmptyValueException
     * @throws UrlValidationException
     */
    private function validateAccessControlAllowOrigin(): void
    {
        $this->stringValidation->notEmpty(
            value: $this->accessControlAllowOrigin
        );

        if (
            !filter_var(
                value: $this->accessControlAllowOrigin,
                filter: FILTER_VALIDATE_URL
            )
        ) {
            throw new UrlValidationException(
                message: 'AccessControlAllowOrigin must be of type URL.'
            );
        }
    }

    /**
     * Validate that data uses proper urls.
     *
     * @throws UrlValidationException
     */
    private function validateProperUrls(): void
    {
        // Since this data is not required, we allow them to be empty.
        if ($this->logoUrl === '' || $this->homepageUrl === '') {
            return;
        }

        UrlValidation::validateMultipleUrls(
            urls: [$this->logoUrl, $this->homepageUrl]
        );
    }
}
