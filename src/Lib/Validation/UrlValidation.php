<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Validation;

use Resursbank\Ecom\Exception\UrlValidationException;

/**
 * Special URL Validation.
 */
class UrlValidation
{
    public static function validateMultipleUrls(array $urls): void
    {
        foreach ($urls as $url) {
            if (
                !filter_var(value: $url, filter: FILTER_VALIDATE_URL)
            ) {
                throw new UrlValidationException(
                    message: 'AccessControlAllowOrigin must be of type URL.'
                );
            }
        }
    }
}
