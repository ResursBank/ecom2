<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesUrl;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer address data from a payment.
 */
class TaskRedirectionUrls extends Model
{
    public function __construct(
        #[StringMatchesUrl] public string $merchantUrl,
        #[StringMatchesUrl] public string $customerUrl,
        #[StringMatchesUrl] public ?string $coApplicantUrl = null
    ) {
    }
}
